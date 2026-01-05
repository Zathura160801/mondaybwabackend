<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductController extends Controller
{
    private $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index()
    {
        $fields = ['id', 'name', 'thumbnail', 'price', 'category_id'];

        $products = $this->productService->getAll($fields);

        return response()->json(ProductResource::collection($products));
    }

    public function show(int $id)
    {
        try {
            $fields = ['id', 'name', 'thumbnail', 'price', 'about', 'category_id'];

            $product = $this->productService->getById($id, $fields);

            return response()->json(new ProductResource($product));
            
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }
    }

    public function store(ProductRequest $request)
    {
        $product = $this->productService->create($request->validated());

        return response()->json(new ProductResource($product), 201);
    }

    public function update(ProductRequest $request, int $id)
    {
        try {
            $product = $this->productService->update($id, $request->validated());

            return response()->json(new ProductResource($product));

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->productService->delete($id);

            return response()->json([
                'message' => 'Product deleted successfully'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }
    }
}
