<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MerchantController;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\MerchantProductController;
use App\Http\Controllers\WarehouseProductController;

Route::post('token-login', [AuthController::class, 'tokenLogin']);
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthController::class, 'user']);
})

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['auth:sanctum', 'role:manager'])->group(function () {
    Route::apiResource('users', UserController::class);
    Route::apiResource('roles', RoleController::class);

    Route::post('users/roles', [UserRoleController::class, 'assignRole']);

    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('products', ProductController::class);

    Route::apiResource('warehouses', WarehouseController::class);
    Route::apiResource('merchants', MerchantController::class);

    Route::post('warehouses/{warehouse}/products', [WarehouseProductController::class, 'attach']);
    Route::delete('warehouses/{warehouse}/products/{product}', [WarehouseProductController::class, 'detach']);
    Route::put('warehouses/{warehouse}/products/{product}', [WarehouseProductController::class, 'update']);

    Route::post('merchants/{merchant}/products', [MerchantProductController::class, 'store']);
    Route::put('merchants/{merchant}/products/{product}', [MerchantProductController::class, 'update']);
    Route::delete('merchants/{merchant}/products/{product}', [MerchantProductController::class, 'destroy']);
});

Route::middleware(['auth:sanctum', 'role:manager|keeper'])->group(function () {
    Route::post('transactions', [TransactionController::class, 'store']);
    Route::get('transactions/{transaction}', [TransactionController::class, 'show']);

    Route::get('my-merchant', [MerchantController::class, 'getMyMerchantProfile']);
    Route::get('my-merchant/transactions', [TransactionController::class, 'getTransactionsByMerchant']);
});
