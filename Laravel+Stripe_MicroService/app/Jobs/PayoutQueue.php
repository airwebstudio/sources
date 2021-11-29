<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


use App\Models\Account;
use App\Models\StripeBalanceTransaction;
use App\Models\PayoutQueueItem;

use Stripe\StripeClient;

use Illuminate\Support\Facades\Http;

class PayoutQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $stripe;
    protected $id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id, StripeClient $stripe)
    {
        $this->stripe = $stripe;
        $this->id = $id;
    }

    private function _get_balance($user_id, StripeClient $stripe) {
        return ($bal = UserBalance::where('user_id', $user_id)->first()) ? $bal->balance($stripe) : ['status' => 'Done', 'available' => 0];
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        if (!$item = PayoutQueueItem::find($this->id)){
            abort(404);        
        }
        try {

            if ($this->_get_balance($item->internal_user_id, $this->stripe)->available > $item->amount) {
                throw new \Exception('Not enought balance');
            }

            $acc = Account::where('internal_user_id', $item->internal_user_id)->first();            

            $res = $this->stripe->transfers->create([
                'amount' => $item->amount*100,
                'currency' => 'usd',
                'destination' => $acc->stripe_account_id,
            ]);

            
            $item->data = json_encode($res, 1);
            

            $bal_trans = $this->stripe->balanceTransactions->retrieve($res->balance_transaction);

            // $user_balace = UserBalance::where('internal_user_id', $item->internal_user_id);
            // $user_balace->available_balance += $bal_trans->amount;

            $bal_item = new StripeBalanceTransaction();

            $bal_item->internal_user_id = $item->internal_user_id;
            $bal_item->balance_transaction_id = $res->balance_transaction;
            $bal_item->available_on = $bal_trans->available_on;
            $bal_item->status = $bal_trans->status;
            $bal_item->amount = $bal_trans->amount;
            $bal_item->fee = $bal_trans->fee;
            $bal_item->type = 'payout';
        
            $item->status = 'Done';
            

            $bal_item->save();
            $item->save();
            
            // \DB::beginTransaction();

            // try {
                
                
            // 

            //     $item->status = 'Fail';
            //     $item->error_data = json_encode($e->__toString());
            //     $item->save();

            //     throw new \Exception('Wrong data');
    
            // }

            // $acc_id = $item->seller_account_id;

            // $aid = Account::find($acc_id)->stripe_account_id;

            // // $data = $this->stripe->accounts->allExternalAccounts(
            // //     $aid,
            // //     ['object' => 'bank_account']
            // // )->data;

            // // if (sizeof($data) == 0)
            // //     throw new \Exception('No external account');
            
            // $amount = $item->amount;
            
            // // $bid = $data[0]->id;

            // //return $data;

            // $res = $this->stripe->payouts->create([
            //     'amount' => $amount,
            //     'currency' => 'usd',
            //     //'destination' => $bid,
            //     //'source_type' => 'bank_account',
            //     //'description' => 'first payout payment transfer on stripe',
            //     //'method' => "instant",
                
            // ], 
            //     ['stripe_account' => $aid]
            // );

            // $item->data = json_encode($res, 1);

            // $bal_trans = $this->stripe->balanceTransactions->retrieve($res->balance_transaction, [], ['stripe_account' => $aid]);

            // $bal_item = new StripeBalanceTransaction();

            // $bal_item->balance_transaction_id = $res->balance_transaction;
            // $bal_item->available_on = $bal_trans->available_on;
            // $bal_item->status = $bal_trans->status;
            // $bal_item->amount = $bal_trans->net;
            // $bal_item->fee = $bal_trans->fee;
            // $bal_item->seller_account_id = $item->seller_account_id;
            // $bal_item->type = 'payout';

            // $bal_item->save();

            // $item->status = 'Done';
            // $item->save();
        }
        catch (\Throwable $e) {
            $item->status = 'Fail';
            $item->error_data = json_encode($e->__toString());
            $item->save();

        }

        $pserver = config('app.public_api_server');
        $item->type = 'payout';
        Http::post($pserver.'/api/stripe/finish_queue', ['queue' => $item]);

    }
}
