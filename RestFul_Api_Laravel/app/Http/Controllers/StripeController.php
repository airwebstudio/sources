<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

use App\Models as Models;


class StripeController extends Controller
{
    private function user_request($api, $params = []) {
        $sserver = config('app.stripe_server');
        if (!$user = Auth::user()) {
            throw new \Exception('No logged user');
        }

        //$user = Models\User::find(1);
        $params = array_merge(request()->all(), $params, ['user' => $user->toArray()]);

        $data = Http::get($sserver.'/api/stripe/'.$api, $params)->json();
        return response()->json($data);
    }
    
    public function connect() {
        return $this->user_request('create');
    }

    public function get() {
        return $this->user_request('get');
    }

    public function link() {
        return $this->user_request('link');
    }

    public function retrieve() {
        return $this->user_request('retrieve');
    }

    public function charge() {
        return $this->user_request('charge');
    }

    public function transaction() {
        return $this->user_request('transaction');
    }

    public function balance() {
        return $this->user_request('balance');
    }

    public function payment_status() {

        if (!(($type = request()->input('type', false)) && ($qid = request()->input('qid', false)))) {
            throw new \Exception('Wrong params');
        }

        if ($type == 'charge') {
            return $this->user_request('payment_status');
        }

        $res = Models\QueueFinished::where('qid', $qid)->where('type', $type)->first();

        return response()->json($res ?? ['status' => 'Pending']);
    }


    public function finish_queue() {
        if (!($res = request()->input('queue', false)))  {
            throw new \Exception('No params for finish queue');
        }
        
        //$res = (object)['id' => '1', 'status' => 'Done', 'buyer_account_id' => '1', 'type' => 'join_meeting', 'meeting_hash' => 'c0f22a84-1fbd-4411-a2eb-5e4472e1998f'];

        try {
            $res = (object)$res;

            Models\QueueFinished::create([
                'qid' => $res->id,
                'type' => $res->type,
                'status' => $res->status,
                'data' => json_encode($res, 1),
            ]);

            if ($res->status != 'Fail') {
                if (($res->type == 'join_meeting') && ($res->status == 'Reserved')) {
                    $item = Models\Meeting::where('hash', $res->meeting_hash)->first();
                    $item->addParticipant($res->buyer_account_id);
                }
            }
            else {

                if ($res->type == 'payment') {
                    if ($res->status == 'Fail') {
                        //Models\Meeting::where('hash', $res->meeting_hash)->removeParticipant($res->buyer_account_id);
                        $vserver = config('app.video_server');
                        Http::post($vserver.'/meeting/paymentstate', ['user_id' => $res->buyer_account_id, 'meeting_hash' => $res->meeting_hash]);
                        
                    }
                }
            }

            
        }
        catch (\Throwable $e) {
            Models\QueueFinished::create([
                'qid' => $res->id,
                'type' => $res->type,
                'status' => 'Fail',

                'data' => $e->__toString(),

            ]);
        }
        

    }

    public function history() {
        return $this->user_request('history');
    }

    public function payout() {
        return $this->user_request('payout');
    }

}
