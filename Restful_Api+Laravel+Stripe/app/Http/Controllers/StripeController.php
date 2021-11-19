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

class StripeController extends Controller
{

    private function stripe_account($user_id) {
        return Account::where('internal_user_id', $user_id)->first();
    }

    private function get_stripe_account() {
        $request = request();
        if ($request->has('user') &&
            ($user = (object)$request->user))
            return $this->stripe_account($user->id);

        throw new \Exception('User not found');
    }

    private function req($stripe, $request, $func) {
        if ($request->has('user') &&
            ($user = (object)$request->user) &&
            ($acc = $this->stripe_account($user->id))
            )
			{
                $data = $func($stripe, $acc);
                return response()->json(['data' => $data]);
            }
        return response()->json(['error' => 'error']);
    }


    public function create_account(StripeClient $stripe, Request $request) {

        if (!$request->has('user')) {
            return response()->json(['error' => 'error']);
        }

        $user = (object)$request->only('user')['user'];

        if ($acc = $this->stripe_account($user->id)) {
            return response()->json(['data' => $acc]);
        }


        $sacc = $stripe->accounts->create([
            'type' => 'standard',
            'country' => 'US',
            'email' => $user->email,
        ]);


        $new_acc = new Account();
        $new_acc->internal_user_id = $user->id;
        $new_acc->stripe_account_id = $sacc->id;
        $new_acc->save();


        return response()->json(['data' => $new_acc->toArray()]);

    }


    public function get_account(StripeClient $stripe, Request $request) {
        if ($request->has('user') &&
            ($user = (object)$request->only('user')['user']) &&
            ($acc = $this->stripe_account($user->id))
            ) {
                return response()->json(['data' => $acc]);
            }
            return $this->create_account($stripe, $request);
    }

    public function get_link(StripeClient $stripe, Request $request) {


            return $this->req($stripe, $request, function($stripe, $acc) {
                $public_server = config('app.public_server');

         return  $stripe->accountLinks->create([
                    'account' => $acc->stripe_account_id,
                    'refresh_url' => $public_server.'/stripe/reauth',
                    'return_url' => $public_server.'/stripe/return',
                    'type' => 'account_onboarding',
                ]);
            });
    }

    public function get_retrieve(StripeClient $stripe, Request $request) {
        return $this->req($stripe, $request, function($stripe, $acc) {
            $res = (object)$stripe->accounts->retrieve(
                $acc->stripe_account_id
            );

            if (!$res->verification->disabled_reason) {
                $acc->verificated = true;
                $acc->save();
            }
            return $res;
        });
    }

    public function charge(StripeClient $stripe, Request $request) {
       
        if(!$request->has('user') ||
           !$request->has('amount') ||
           !$request->has('card_data')

        )
            throw new \Exception('No fields');


        $user = (object)$request->user;
        $amount = $request->only('amount')['amount'];
        $card_data = $request->only('card_data')['card_data'];


        try {
            $qitem = new TransactionQueueItem();
            $qitem->amount = $amount;
            $qitem->internal_user_id = $user->id;
            $qitem->card_data = json_encode($card_data);
            $qitem->save();


            Jobs\ProcessQueueItem::dispatch($qitem->id, $stripe);
        }
        catch (\Throwable $e) {
            //return $e->getMessage();
            return response()->json(['error' => $e->__toString()], 500);
        }

        return response()->json(['qid' => $qitem->id, 'type' => 'charge']);

    }

    public function reserve(StripeClient $stripe, Request $request) {

        if (!$request->has('user'))
            throw new \Exception('No user');

        if (!$request->has('amount'))
            throw new \Exception('No transaction amount');

        if (!$request->has('seller_id'))
            throw new \Exception('No seller id');

        if (!$request->has('meeting_hash'))
            throw new \Exception('No meeting hash');


        $expired_date = ($request->has('expired_date')) ? $request->expired_date : date('Y-m-d H:i:s', strtotime('+1 day'));



        $user = (object)$request->user;
        //$user = (object)['id' => 1];
        $amount = $request->amount;
        $seller_id = $request->seller_id;
        $meeting_hash = $request->meeting_hash;
        $type = $request->input('type', 'reserve');


        $bal = $this->_get_balance($user->id, $stripe);


        if ($bal->available < $amount) {
            throw new \Exception('Not enought balance');
        }

        $item = new BuyingQueueItem();

        $item->buyer_account_id = $user->id;
        $item->amount = $amount;
        $item->seller_account_id = $seller_id;
        $item->meeting_hash = $meeting_hash;
        $item->expired_date = $expired_date;
        $item->type = $type;

        $item->save();

        $qid = $item->id;

        Jobs\BuyingQueueItem::dispatch($qid, 'reserve', $stripe);

        return response()->json(['qid' => $qid, 'type' => $type]);

    }


    public function approve_reserve(StripeClient $stripe, Request $request) {

        if (!$request->has('meeting_hash'))
            throw new \Exception('No meeting hash');

        $type = $request->input('type', 'approve_reserve');


        $all = BuyingQueueItem::where('meeting_hash', $request->meeting_hash)
            //where('seller_account_id', $user->id)
            ->where('status', 'Reserved');


        if ($users = $request->input('users', false)) {

            $users = json_decode($users);
            $users_sort = [];

            $userids = [];
            foreach ((array)$users as $u) {
                $userids[] = $u->user_id;
                $users_sort[$u->user_id] = $u;
            }

            $all = $all->where('buyer_account_id', $userids);

        }

        $all = $all->get();

        $qids = [];

        foreach($all as $one) {

            $one->status = 'Process';
            $one->save();

            Jobs\BuyingQueueItem::dispatch($one->id, 'approve', $stripe);
            $qids[] = $one->id;
        }

        return response()->json(['status' => 'Done', 'type' => $type, 'qids' => $qids]);

    }

    public function get_stripe_account_balance(StripeClient $stripe, Request $request) {
        if ($acc = $this->get_stripe_account()) {
            $res = $stripe->balance->retrieve([], ['stripe_account' => $acc->stripe_account_id]);
            $res->account_id = $acc->stripe_account_id;
            return $res;
        }
        return response()->json(['error' => 'error']);
    }


    private function _get_balance($user_id, StripeClient $stripe) {
        return ($bal = UserBalance::where('user_id', $user_id)->first()) ? $bal->balance($stripe) : (object) ['status' => 'Done', 'available' => 0];
    }


    public function get_balance(StripeClient $stripe, Request $request, $user_id) {
        return  response()->json($this->_get_balance($user_id, $stripe));
    }

    public function make_payout(StripeClient $stripe, Request $request) {
        if (!$request->has('user'))
            throw new \Exception('No user');

        if (!$request->has('amount'))
            throw new \Exception('No transaction amount');


        $user = (object)$request->user;



        $amount= $request->amount;

        $item = new PayoutQueueItem();

        $item->amount = $amount;
        $item->internal_user_id = $user->id;
        //$item->status = 'Proccess';

        $item->save();

        $qid = $item->id;

        Jobs\PayoutQueue::dispatch($qid, $stripe);

        return response()->json(['qid' => $qid, 'type' => 'payout']);

    }

    public function refund(StripeClient $stripe) {

        return $stripe->refunds->create([
            'payment_intent' => "pi_1IoYaTIhZYZ7KT5fmCpyyJDU",
        ]);
    }

    public function get_payment_status(Request $request, StripeClient $stripe) {

        if (!$request->has('user'))
            throw new \Exception('No user');

        $user = (object)$request->user;


        if (!$qid = $request->qid)
            throw new \Exception('No queue id');

        $type = $request->input('type', 'charge');


        if ($type == 'charge') {
            $item = TransactionQueueItem::find($qid);


            $status = $item->status;
            $source = json_decode($item->source_data);


            $res = $item;

            if ($status == 'Pending') {
                $res['url'] = $source->redirect->url;
                $source = $stripe->sources->retrieve($source->id);

                $res['auth'] = $source->three_d_secure->authenticated;
                $res['auth_status'] = $source->redirect->status;

                if ($source->three_d_secure->authenticated && $source->redirect->status == 'succeeded') {
                    $status = 'AuthDone';

                    $qitem = TransactionQueueItem::find($qid);
                    $qitem->status = $status;
                    $qitem->save();

                    Jobs\ProcessQueueItem::dispatch($qid, $stripe); //retry after 3d auth
                }
            }


            $res['status'] = $status;

            return response()->json($res);

        }

        elseif (($type == 'join_meeting') || ($type == 'finish_meeting')) {
            return response()->json(BuyingQueueItem::find($qid));
        }

        elseif ($type == 'payout') {
            return response()->json(PayoutQueueItem::find($qid));
        }

        return response()->json(['error' => 'error']);
    }

    public function get_accounts(StripeClient $stripe, Request $request) {
        return $stripe->accounts->all();
    }

    public function get_history(Request $request) {

        if (!$request->has('user'))
            throw new \Exception('No user');
        $user = (object)$request->user;


        $items = StripeBalanceTransaction::where('internal_user_id', $user->id)->get();
        $res = ['stripe' => $items];

        foreach ($items as $item) {
            $res[$item->type][] = $item;
        }

        $items = WalletTransaction::where('buyer_account_id', $user->id)->get();

        foreach ($items as $item) {
            $res['buying'][] = $item;
        }


        $items = WalletTransaction::where('seller_account_id', $user->id)->get();

        foreach ($items as $item) {
            $res['selling'][] = $item;
        }

        // if (($acc = Account::where('internal_user_id', $user->id)->first()) && (!empty($acc)) &&
        //     ($items = StripeBalanceTransaction::where('seller_account_id', $acc->id)->get()) &&
        //     (isset($items))
        // ) {

        //   $res['seller'] = $items;

        // }

        return response()->json($res);

    }

    public function payment(StripeClient $stripe, Request $request) {


        $amount = $request->amount;
        $seller_id = $request->seller_id;
        $meeting_hash = $request->meeting_hash;

        $user = (object)$request->user;


        if (!$payments = $request->input('payments', false)) {
            return response()->json();
        }

        $qids = []; 

        //return $payments;
       
        foreach ($payments as $p) {
            $p = (object)$p;

            $user_id = (isset($p->user_id)) ? $p->user_id : $user->id;

            $bal = $this->_get_balance($user_id, $stripe);

            if ($bal->available < $p->amount) {
                throw new \Exception('Not enough balance');
            }

            $item = new BuyingQueueItem();

            $item->buyer_account_id = $user_id;
            $item->amount = $p->amount;
            $item->seller_account_id = $p->seller_id;
            $item->meeting_hash = $p->meeting_hash;
            $item->type = 'payment';
            $item->description = $p->description;

            $item->save();

            $qid = $item->id;

            Jobs\BuyingQueueItem::dispatch($qid, 'payment', $stripe);
            $qids[] = $qid;

        }
        
            
        return response()->json(['qids' => $qids, 'type' => 'payment']);
    }

    
    public function start_payment(StripeClient $stripe, Request $request) {
        $user = (object)$request->user;
        $amount = $request->amount;
        $seller_id = $request->seller_id;
        $meeting_hash = $request->meeting_hash;


        $bal = $this->_get_balance($user->id, $stripe);


        if ($bal->available < $amount) {
            throw new \Exception('Not enough balance');
        }

        $item = new BuyingQueueItem();

        $item->buyer_account_id = $user->id;
        $item->amount = $amount;
        $item->seller_account_id = $seller_id;
        $item->meeting_hash = $meeting_hash;
        $item->type = 'join_meeting';

        $item->save();

        $qid = $item->id;

        Jobs\BuyingQueueItem::dispatch($qid, 'payment', $stripe);

        return response()->json(['qid' => $qid, 'type' => 'join_meeeting']);
    }


    private function formate_date($arr) {
        foreach ($arr as $a) {
             if (isset($a->created_at)) {
                $a->created_date = date('Y-m-d H:i:s', strtotime($a->created_at));
             }

             if (isset($a->updated_at)) {
                $a->updated_date = date('Y-m-d H:i:s', strtotime($a->updated_at));
             }
        }

        return $arr;
    }

    public function payments_list(Request $request) {


        if (!$user = $request->input('user', false))
            throw new \Exception('No user');

        if (!$hash = $request->input('meeting_hash', false)) {
            throw new \Exception('No meeting hash');
        }

        $user = (object)$user;

        $res = [];

        $res['seller_transactions'] = WalletTransaction::where('meeting_hash', $hash)->where('seller_account_id', $user->id)->get();

        $res['buyer_transactions'] = WalletTransaction::where('meeting_hash', $hash)->where('buyer_account_id', $user->id)->get();
        $res['seller_sum'] = WalletTransaction::where('meeting_hash', $hash)->where('seller_account_id', $user->id)->sum('amount');

        if ($res['seller_sum'])
            $res['buyers_sum'] = WalletTransaction::where('meeting_hash', $hash)->selectRaw('sum(amount) as sum, buyer_account_id')->where('seller_account_id', $user->id)->groupBy('buyer_account_id')->get();

        $res['buyer_sum'] = WalletTransaction::where('meeting_hash', $hash)->where('buyer_account_id', $user->id)->sum('amount');

        if ($res['buyer_sum']) {
            $res['buyer_reservs'] = WalletReserveTransaction::where('meeting_hash', $hash)->where('buyer_account_id', $user->id)->get();
            $res['buyer_reservs_sum'] = WalletReserveTransaction::where('meeting_hash', $hash)->where('buyer_account_id', $user->id)->sum('amount');
        }
        return response()->json($res);
    }
}
