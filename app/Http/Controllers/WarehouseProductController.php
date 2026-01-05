<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WarehouseService;
use App\Http\Requests\WarehouseProductUpdateRequest;

class WarehouseProductController extends Controller
{
    private $warehouseService;

    public function __construct(WarehouseService $warehouseService)
    {
        $this->warehouseService = $warehouseService;
    }

    public function attach(Request $request, int $warehouseId): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'stock' => 'required|integer|min:1',
        ]);

        $this->warehouseService->attachProduct(
            $warehouseId,
            $request->input('product_id'),
            $request->input('stock')
        );

        return response()->json(['message' => 'Product attached successfully.']);
    }

    public function detach(int $warehouseId, int $productId): JsonResponse
    {
        $this->warehouseService->detachProduct($warehouseId, $productId);

        return response()->json(['message' => 'Product detached successfully.']);
    }

    public function update(WarehouseProductUpdateRequest $request, int $warehouseId, int $productId): JsonResponse
    {
        $warehouseProduct = $this->warehouseService->updateProductStock(
            $warehouseId,
            $productId,
            $request->validated()['stock']
        );

        return response()->json([
            'message' => 'Stock updated successfully.',
            'data' => $warehouseProduct,
        ]);
    }
}
