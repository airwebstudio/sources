<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\TransactionQueueItem;
use App\Models\StripeBalanceTransaction;
use App\Models\UserBalance;

use Stripe\StripeClient;

use Illuminate\Support\Facades\Http;

class ProcessQueueItem implements ShouldQueue
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

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!$item = TransactionQueueItem::find($this->id)) {
            abort(404);
        }

        try {


                if ($item->status == 'AuthDone') {
                    $sdata = json_decode($item->source_data);
                    $source = $this->stripe->sources->retrieve($sdata->id, []);
                } 
                else {
                    $source = $this->stripe->sources->create([
                        'type' => 'card',
                        'card' => (array)json_decode($item->card_data),
                    ]);   
                }

                if (($item->status == 'AuthDone') || ($source->card->three_d_secure !== 'required')) {

                    $params = [
                        'amount' => $item->amount * 100,
                        'currency' => $item->currency,
                        'source' => $source->id,
                        //'setup_future_usage' => 'off_session',
                        // 'confirm' => true,
                        // 'off_session' => true,
                        // 'transfer_data' => [
                        //     'destination' => $item->stripe_account_id,
                        // ],
                    ];

                    if ($item->status == 'AuthDone') {
                        //$params['payment_method_types'] = ['three_d_secure'];
                        // $params['source']['three_d_secure'] = $source->id;
                        // $params['source']['three_d_secure']['card'] = $source->id;
                    }

                    $payment = $this->stripe->charges->create( $params);

                    $bal_trans = $this->stripe->balanceTransactions->retrieve($payment->balance_transaction);

                    $bal_item = new StripeBalanceTransaction();

                    $bal_item->internal_user_id = $item->internal_user_id;
                    $bal_item->balance_transaction_id = $payment->balance_transaction;
                    $bal_item->available_on = $bal_trans->available_on;
                    $bal_item->status = $bal_trans->status;
                    $bal_item->amount = $bal_trans->amount / 100;
                    $bal_item->fee = $bal_trans->fee / 100;

                    $bal_item->save();


                    // $user_balance = UserBalance::where('internal_user_id', $item->internal_user_id)->first();
                    // if (!$user_balance) {
                    //     $user_balance = new UserBalance();
                    //     $user_balance->internal_user_id = $item->internal_user_id;
                    // }
                    

                    // if ($bal_trans->status == 'available') {
                    //     $user_balance->available_balance += $bal_trans->net;
                    // }
                    // else {
                    //     $user_balance->pending_balance += $bal_trans->net;
                    // }

                    //$user_balance->save();

                    //var_dump($bal_trans);

                    //$payment = $this->stripe->paymentIntents->create($params);

                    $item->payment_data = json_encode($payment);
                    $item->status = ($payment->status == 'succeeded') ? 'Done' : 'Fail';
                }
                else {

                    $spub = config('app.public_server');
                    $source = $this->stripe->sources->create([
                        'amount' => $item->amount * 100,
                        'currency' => $item->currency,
                        'type' => 'three_d_secure',
                        'three_d_secure' => [
                            'card' => $source->id,
                        ],
                        'redirect' => [
                            'return_url' => $spub.'/stripe/payment'
                        ],
                    ]);
    
                    $item->source_data = json_encode($source, 1);
    
                    $item->status = 'Pending';

                }

                // $payment_method = $this->stripe->paymentMethods->create([
                //     'type' => 'card',
                //     'card' => (array)json_decode($item->card_data),
                // ]);

                $item->save();

                if ($item->status == 'Done') {
                    if (!UserBalance::where('user_id', $item->internal_user_id)->first()) {
                        UserBalance::create(['user_id' => $item->internal_user_id]);
                    }
                }

            
        }

        catch (\Throwable $e) {
            $item->status = 'Fail';
            $item->error_data = json_encode($e->__toString());
            $item->save();

        }

        $pserver = config('app.public_api_server');
        $item->type = 'charge';


        $res = Http::post($pserver.'/api/stripe/finish_queue', ['queue' => $item]);

    }
}
