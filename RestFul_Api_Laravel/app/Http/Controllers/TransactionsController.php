<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Seller;

class TransactionsController extends Controller
{

    /**
     * @OA\Put(
     *     tags={"transaction"},
     *     path="/api/transaction",
     *     summary="Create transaction",
     *
     *     @OA\RequestBody(
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="description", type="string", format="description", example="description"),
     *              @OA\Property(property="amount", type="string", format="amount", example="5"),
     *          ),
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="response",
     *          @OA\JsonContent(
     *              @OA\Property(property="result", type="string", example="success"),
     *              @OA\Property(property="internal_transaction_id", type="string", example="test_1617526437"),
     *              @OA\Property(property="response", type="object",
     *                  @OA\Property(property="cookies", type="object"),
     *                  @OA\Property(property="transferStats", type="object"),
     *              )
     *          )
     *      )
     * )
     */
    public function create(Request $request){
        $streamer = Seller::first();
        $internal_transaction_id = 'test_'.time();
        $transaction = [
            'type' => 'PAYMENT',
            'internal_transaction_id' => $internal_transaction_id,
            'payment_system' => $streamer->payment_system,
            'description' => $request->input('description'),
            'amount' => $request->input('amount'),
            'currency' => 'USD',
            'items' => [],
            'credentials' => $streamer->credentials
        ];
        if(getenv('FINANCE_API_USER')){
            $response = Http::withBasicAuth(getenv('FINANCE_API_USER'), getenv('FINANCE_API_PASS'))
                ->put(getenv("FINANCE_API_URL").'/api/transaction', $transaction);
        }else{
            $response = Http::put(getenv("FINANCE_API_URL").'/api/transaction', $transaction);
        }


        return response()->json(['result' => 'success', 'internal_transaction_id' => $internal_transaction_id, 'response' => $response]);
    }

    /**
     * @OA\Get (
     *     tags={"transaction"},
     *     path="/api/transaction/{internal_transaction_id}",
     *     summary="Get transaction",
     *
     *     @OA\RequestBody(
     *          description="",
     *          @OA\JsonContent(
     *              required={"internal_transaction_id"},
     *              @OA\Property(property="internal_transaction_id", type="string", format="internal_transaction_id", example="test_1617526437"),
     *          ),
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="response",
     *          @OA\JsonContent(
     *
     *          )
     *      )
     * )
     */
    public function info(Request $request, $internal_transaction_id){
        if(getenv('FINANCE_API_USER')){
            $response = Http::withBasicAuth(getenv('FINANCE_API_USER'), getenv('FINANCE_API_PASS'))
                ->get(getenv("FINANCE_API_URL").'/api/transaction/'.$internal_transaction_id);
        }else{
            $response = Http::get(getenv("FINANCE_API_URL").'/api/transaction/'.$internal_transaction_id);
        }
        return $response;
    }

    /**
     * @OA\Get(
     *     tags={"transaction"},
     *     path="/api/transactions",
     *     summary="Get transactions",
     *
     *     @OA\Response(
     *          response=200,
     *          description="response",
     *          @OA\JsonContent(
     *
     *          )
     *      )
     * )
     */
    public function list(Request $request){
        if(getenv('FINANCE_API_USER')){
            $response = Http::withBasicAuth(getenv('FINANCE_API_USER'), getenv('FINANCE_API_PASS'))
                ->get(getenv("FINANCE_API_URL").'/api/transactions');
        }else{
            $response = Http::get(getenv("FINANCE_API_URL").'/api/transactions');
        }
        return $response;
    }


}
