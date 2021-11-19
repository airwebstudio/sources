<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get('/login', [AuthenticatedSessionController::class, 'store']);

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::put('/transaction', [App\Http\Controllers\TransactionsController::class, 'create']);
Route::get('/transaction/{internal_transaction_id}', [App\Http\Controllers\TransactionsController::class, 'info']);
Route::get('/transactions', [App\Http\Controllers\TransactionsController::class, 'list']);

Route::post('/paypal/update/{id}', [App\Http\Controllers\PayPalController::class, 'update']);

Route::any('/balance/{user_id}', [App\Http\Controllers\BalanceController::class, 'list']);
Route::any('/wallet/sales/{user_id}', [App\Http\Controllers\WalletController::class, 'sales']);
Route::any('/wallet/purchases/{user_id}', [App\Http\Controllers\WalletController::class, 'purchases']);

Route::put('/queue', [App\Http\Controllers\Queue::class, 'add']);
Route::any('/stripe/get', [App\Http\Controllers\StripeController::class, 'get_account']);
Route::any('/stripe/create', [App\Http\Controllers\StripeController::class, 'create_account']);
Route::any('/stripe/link', [App\Http\Controllers\StripeController::class, 'get_link']);
Route::any('/stripe/retrieve', [App\Http\Controllers\StripeController::class, 'get_retrieve']);
Route::any('/stripe/charge', [App\Http\Controllers\StripeController::class, 'charge']);
Route::any('/stripe/reserve', [App\Http\Controllers\StripeController::class, 'reserve']);
Route::any('/stripe/approve_reserve', [App\Http\Controllers\StripeController::class, 'approve_reserve']);
Route::get('/stripe/balance/{id}', [App\Http\Controllers\StripeController::class, 'get_balance']);
Route::any('/stripe/payout', [App\Http\Controllers\StripeController::class, 'make_payout']);
Route::any('/stripe/refund', [App\Http\Controllers\StripeController::class, 'refund']);
Route::any('/stripe/accounts', [App\Http\Controllers\StripeController::class, 'get_accounts']);
Route::any('/stripe/payment_status', [App\Http\Controllers\StripeController::class, 'get_payment_status']);
Route::any('/stripe/history', [App\Http\Controllers\StripeController::class, 'get_history']);
Route::any('/stripe/payment', [App\Http\Controllers\StripeController::class, 'payment']);
Route::any('/stripe/payments_list', [App\Http\Controllers\StripeController::class, 'payments_list']);
