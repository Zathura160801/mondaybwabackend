<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TransactionService;
use App\Http\Requests\TransactionRequest;
use App\Http\Resources\TransactionResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TransactionController extends Controller
{
    private $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function index()
    {
        $fields = ['*'];

        $transactions = $this->transactionService->getAll($fields);

        return response()->json(TransactionResource::collection($transactions));
    }

    public function store(TransactionRequest $request)
    {
        $transaction = $this->transactionService->createTransaction($request->validated());

        return response()->json([
            'message' => 'Transaction created successfully',
            'data' => $transaction,
        ], 201);
    }

    public function show(int $id)
    {
        try {
            $fields = ['*'];

            $transaction = $this->transactionService->getTransactionById($id, $fields);

            return response()->json(new TransactionResource($transaction));
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Transaction not found'
            ], 404);
        }
    }

    public function getTransactionsByMerchant()
    {
        $user = auth()->user();

        if (!$user || !$user->merchant) {
            return response()->json([
                'message' => 'No merchant assigned'
            ], 403);
        }

        $merchantId = $user->merchant->id;
        $transactions = $this->transactionService->getTransactionsByMerchant($merchantId);

        return response()->json($transactions);
    }
}
