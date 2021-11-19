<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *    title="UserBalance",
 *    @OA\Property(property="id", type="int", format="id", example=""),
 *    @OA\Property(property="user_id", type="int", format="user_id", example=""),
 *    @OA\Property(property="created_at", type="\Carbon\Carbon|null", format="created_at", example=""),
 *    @OA\Property(property="updated_at", type="\Carbon\Carbon|null", format="updated_at", example=""),
 *
 * )
 */
class UserBalance extends Model
{
	protected $table = 'user_balance';

	protected $casts = [
		'user_id' => 'int'
	];

	protected $fillable = [
		'user_id'
	];

	public function balance($stripe) {
		$pending = StripeBalanceTransaction::where('internal_user_id', $this->user_id)->where('status', 'pending')->whereDate('available_on', '<=', date('Y-m-d H:i:s'))->get();


        foreach($pending as $p) {
            $bal_trans = $stripe->balanceTransactions->retrieve($p->balance_transaction_id);

            if ($bal_trans->status && ($bal_trans->status == 'available')) {
                $p->status = $bal_trans->status;
                // $p->amount = $bal_trans->net;
                // $p->fee = $bal_trans->fee;
                $p->save();

                // if ($user_balance->pending_balance >= $p->amount) {
                //     $user_balance->available_balance += $p->amount;
                //     $user_balance->pending_balance -= $p->amount;

                //     $user_balance->save();
                // }

            }

        //}

        }


        $exp_reserves = WalletReserveTransaction::where('buyer_account_id', $this->user_id)->whereDate('expired_date', '<=', date('Y-m-d H:i:s'))->get();

        if ($exp_reserves) {
            \DB::beginTransaction(); //everything in one transaction

            try {
                foreach ($exp_reserves as $r) {

                    $w_item = $r->replicate();
                    $w_item->amount = -$w_item->amount;
                    $w_item->description = 'Canceled meeting #'.$w_item->meeting_hash.' pay';


                    $r->expired_date = null;
                    $r->save();
                    $w_item->save();


                }

                \DB::commit();
            }

            catch (\Throwable $e) {
                DB::rollback();
                throw $e;
            }
        }



        $bal_res = StripeBalanceTransaction::where('internal_user_id', $this->user_id)
        ->selectRaw('sum(amount) as sum, status')->groupBy('status')->get();

        $bal_buy_wallet = (object)WalletTransaction::where('buyer_account_id', $this->user_id)
        ->selectRaw('sum(amount) as sum')->first();

        $bal_sell_wallet = WalletTransaction::where('seller_account_id', $this->user_id)
        ->selectRaw('sum(amount) as sum')->first();


        $bal_buy_reserved_wallet = WalletReserveTransaction::where('buyer_account_id', $this->user_id)
        ->selectRaw('sum(amount) as sum')->first();

        $bal_sell_reserved_wallet = WalletReserveTransaction::where('seller_account_id', $this->user_id)
        ->selectRaw('sum(amount) as sum')->first();



        $res = ['status' => 'Done'];

        foreach ($bal_res as $r) {
            $res[$r->status] = (float)$r->sum;

        }

        if (!isset($res['available']))
            $res['available'] = 0;


        $res['available'] += $bal_sell_wallet->sum;
        $res['available'] -= $bal_buy_wallet->sum;

        $res['available'] -= $bal_buy_reserved_wallet->sum;

        if ($bal_buy_reserved_wallet->sum > 0)
            $res['reserved'] = $bal_buy_reserved_wallet->sum;

        if ($bal_sell_reserved_wallet->sum > 0) {

            if (!isset($res['pending'])) {
                $res['pending'] = 0;
            }

            $res['pending'] += $bal_sell_reserved_wallet->sum;
        }


        return (object)$res;
	}
}
