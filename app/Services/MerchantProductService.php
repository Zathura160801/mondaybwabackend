<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Repositories\MerchantRepository;
use Illuminate\Validation\ValidationException;
use App\Repositories\MerchantProductRepository;
use App\Repositories\WarehouseProductRepository;

class MerchantProductService
{
    private $merchantRepository;
    private $merchantProductRepository;
    private $warehouseProductRepository;

    public function __construct(MerchantRepository $merchantRepository, MerchantProductRepository $merchantProductRepository, WarehouseProductRepository $warehouseProductRepository)
    {
        $this->merchantRepository = $merchantRepository;
        $this->merchantProductRepository = $merchantProductRepository;
        $this->warehouseProductRepository = $warehouseProductRepository;
    }

    public function assignProductToMerchant(array $data)
    {
        return DB::transaction(function () use ($data) {
            
          $warehouseProduct = $this->warehouseProductRepository->getByWarehouseAndProduct($data['warehouse_id'], $data['product_id']);

          if (!$warehouseProduct || $warehouseProduct->stock < $data['stock']) {
              throw ValidationException::withMessages([
                  'stock' => ['Insufficient stock in warehouse.'],
              ]);
          }

          $existingProduct = $this->merchantProductRepository->getByMerchantAndProduct($data['merchant_id'], $data['product_id']);

          if (!$existingProduct) {
              throw ValidationException::withMessages([
                  'product' => ['Product already exists in this merchant.'],
              ]);
          }

          // kurangi stock produk tersebut pada warehouse terkait
          $this->warehouseProductRepository->updateStock($data['warehouse_id'], $data['product_id'], $warehouseProduct->stock - $data['stock']);

          return $this->merchantProductRepository->create([
              'warehouse_id' => $data['warehouse_id'],
              'merchant_id' => $data['merchant_id'],
              'product_id' => $data['product_id'],
              'stock' => $data['stock'],
          ]);
          
        });
    }

    public function updateStock(int $merchantId, int $productId, int $newStock, int $warehouseId)
    {
        return DB::transaction(function () use ($merchantId, $productId, $newStock, $warehouseId) {

            $existing = $this->merchantProductRepository->getByMerchantAndProduct($merchantId, $productId);

            if (!$existing) {
                throw ValidationException::withMessages([
                    'product' => ['Product not assigned to this merchant.'],
                ]);
            }

            if (!$warehouseId) {
                throw ValidationException::withMessages([
                    'warehouse_id' => ['Warehouse ID is required when increasing stock.'],
                ]);
            }

            // stok produk tersebut ada di merchant
            $currentStock = $existing->stock;

            if ($newStock > $currentStock){
                
                $diff = $newStock - $currentStock;

                $warehouseProduct = $this->warehouseProductRepository->getByWarehouseAndProduct($warehouseId, $productId);

                if(!$warehouseProduct || $warehouseProduct->stock < $diff) {
                    throw ValidationException::withMessages([
                        'stock' => ['Insufficient stock in warehouse.'],
                    ]);
                }

                $this->warehouseProductRepository->updateStock($warehouseId, $productId, $warehouseProduct->stock - $diff);
            }

            if ($newStock < $currentStock){

                $diff = $currentStock - $newStock;

                $warehouseProduct = $this->warehouseProductRepository->getByWarehouseAndProduct($warehouseId, $productId);

                if(!$warehouseProduct) {
                    throw ValidationException::withMessages([
                        'warehouse' => ['Warehouse not found in warehouse.'],
                    ]);
                }

                $this->warehouseProductRepository->updateStock($warehouseId, $productId, $warehouseProduct->stock + $diff);
            }

            return $this->updateStock($merchantId, $productId, $newStock);
        };
    }
    
    public function removeProductFromMerchant(int $merchantId, int $productId)
    {
        $merchant = $this->merchantRepository->getById($merchantId, $fields ?? ['*']);

        if (!$merchant) {
            throw ValidationException::withMessages([
                'merchant' => ['Merchant not found.'],
            ]);
        }

        $exists = $this->merchantProductRepository->getByMerchantAndProduct($merchantId, $productId);

        if (!$exists) {
            throw ValidationException::withMessages([
                'product' => ['Product not assigned to this merchant.'],
            ]);
        }

        $merchant->products()->detach($productId);
    }
}