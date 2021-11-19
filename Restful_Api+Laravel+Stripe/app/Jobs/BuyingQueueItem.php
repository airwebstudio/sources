<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Account;
use App\Models\UserBalance;
use App\Models\BuyingQueueItem as BuyingQueueItemModel;
use App\Models\StripeBalanceTransaction;

use App\Models\WalletReserveTransaction;
use App\Models\WalletTransaction;

use Stripe\StripeClient;

use Illuminate\Support\Facades\Http;

class BuyingQueueItem implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $stripe;
    protected $id;
    protected $action;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id, $action, StripeClient $stripe)
    {
        $this->stripe = $stripe;
        $this->id = $id;
        $this->action = $action;
    }

    private function _get_balance($user_id, StripeClient $stripe) {
        return ($bal = UserBalance::where('user_id', $user_id)->first()) ? $bal->balance($stripe) : (object)['status' => 'Done', 'available' => 0];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {


        if (!$item = BuyingQueueItemModel::find($this->id)){
            abort(404);        
        }

        try {

            
            if ($this->action == 'reserve') {

                if ($this->_get_balance($item->buyer_account_id, $this->stripe)->available < $item->amount) {
                    throw new \Exception('Not enough balance');
                }

                $w_item = new WalletReserveTransaction();

                $w_item->buyer_account_id = $item->buyer_account_id;
                $w_item->seller_account_id = $item->seller_account_id;
                $w_item->meeting_hash = $item->meeting_hash;
                $w_item->amount = $item->amount;
                $w_item->expired_date = $item->expired_date;

                $w_item->description = 'Reservervation for meeting '.$item->meeting_hash;

                $w_item->save();

                $item->status = 'Reserved';
                $item->save();
            }
            elseif ($this->action == 'approve') {

                \DB::beginTransaction();

                try {

                    $origial_reserve = WalletReserveTransaction::where([['meeting_hash', $item->meeting_hash], ['seller_account_id', $item->seller_account_id], ['buyer_account_id', $item->buyer_account_id]]);

                    $origial_reserve->update(['expired_date' => null]);

                    $origial_reserve = $origial_reserve->first();

                    $reservation_id = ($origial_reserve) ? $origial_reserve->id : null;

                    $w_item = new WalletReserveTransaction();

                    $w_item->buyer_account_id = $item->buyer_account_id;
                    $w_item->seller_account_id = $item->seller_account_id;
                    $w_item->meeting_hash = $item->meeting_hash;
                    $w_item->amount = -$item->amount;

                    $w_item->description = 'Approved reservervation #'.$reservation_id.' for meeting #'.$item->meeting_hash;

                    $w_item->save();


                    $w_item = new WalletTransaction();

                    $w_item->buyer_account_id = $item->buyer_account_id;
                    $w_item->seller_account_id = $item->seller_account_id;
                    $w_item->meeting_hash = $item->meeting_hash;
                    $w_item->amount = $item->amount;
                    $w_item->description = 'Approved reservervation #'.$reservation_id.' for meeting #'.$item->meeting_hash;

                    $w_item->reservation_id = $reservation_id;

                    $w_item->save();


                    \DB::commit();
                }
                catch (\Throwable $e) {
                    \DB::rollback();

                    throw new \Exception('Not saved');
                }
                

                $item->status = 'Done';
                $item->save();


            }
            elseif ($this->action == 'payment') {

                if ($this->_get_balance($item->buyer_account_id, $this->stripe)->available < $item->amount) {
                    throw new \Exception('Not enough balance');
                }

                $w_item = new WalletTransaction();

                $w_item->buyer_account_id = $item->buyer_account_id;
                $w_item->seller_account_id = $item->seller_account_id;
                $w_item->meeting_hash = $item->meeting_hash;
                $w_item->amount = $item->amount;
                $w_item->description = $item->description;

                $w_item->save();

                $item->status = 'Done';
                $item->save();

            }

            

        }
        catch (\Throwable $e) {
            $item->status = 'Fail';
            $item->error_data = json_encode($e->__toString());
            $item->save();

        }

        $pserver = config('app.public_api_server');
        Http::post($pserver.'/api/stripe/finish_queue', ['queue' => $item]);

    }
}
