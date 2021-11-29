<?php

use App\Http\Controllers\MainController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [MainController::class, 'index']);

Route::put('/v1/transaction/{params}', [MainController::class, 'put_transaction']);
Route::get('/v1/transaction/{id}', [MainController::class, 'get_transaction']);
Route::get('/v1/transactions', [MainController::class, 'get_transactions']);
Route::get('/v1/transactions/queue', [MainController::class, 'get_transactions_queue']);
