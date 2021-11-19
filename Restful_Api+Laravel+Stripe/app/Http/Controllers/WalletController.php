<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use App\Models\Account;
use App\Models\TransactionQueueItem;
use App\Models\StripeBalanceTransaction;
use App\Models\UserBalance;
use App\Models\BuyingQueueItem;
use App\Models\PayoutQueueItem;
use App\Models\WalletReserveTransaction;
use App\Models\WalletTransaction;


use Stripe\StripeClient;

use Illuminate\Support\Facades\Redis;

use App\Jobs as Jobs;

class WalletController extends Controller
{

    public function sales(Request $request, $user_id) {
        $conditions = [
            ['seller_account_id', '=', $user_id]
        ];

        return response()->json(WalletTransaction::where($conditions)->paginate($request->input('limit', 10)));
    }

    public function purchases(Request $request, $user_id) {
        $conditions = [
            ['buyer_account_id', '=', $user_id]
        ];

        return response()->json(WalletTransaction::where($conditions)->paginate($request->input('limit', 10)));
    }
}
