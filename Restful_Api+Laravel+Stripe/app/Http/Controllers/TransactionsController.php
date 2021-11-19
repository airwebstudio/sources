<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TransactionsController extends Controller
{

    public function create(Request $request){
        /*
        Maybe need validate received data
        */
        \App\Jobs\ProcessTransaction::dispatch($request->all())->onQueue('transactions');

        /*
        Need add some info about added item in queue
        */
        return response()->json(['result' => 'success']);
    }

    public function info($_id){
        /*
        Select from database info about transaction & payment/refund/subscription
        by <id> of transaction, received in URL
        */

        list($type, $id) = explode('_', $_id);

        if ($type == 'charge')
            $transaction = \App\Models\StripeBalanceTransaction::find($id);
        if (in_array($type, ['buying', 'selling']))
            $transaction = \App\Models\WalletTransaction::find($id);

        if (!isset($transaction)) {
            throw new \Exception('Wrong params');
        }

        return response()->json($transaction->toArray());
    }

    public function list(Request $request){
        /*
        Return list of transactions based on filter in request
        */


        $charges = \App\Models\StripeBalanceTransaction::select(\DB::raw('CONCAT("charge_", `id`) as id, available_on as date, type, amount, internal_user_id as user_id'));
        $buyings = \App\Models\WalletTransaction::selectRaw(\DB::raw('CONCAT("buying_", `id`) as id, updated_at as date, "buying" as type, -amount as amount, buyer_account_id as user_id'));
        $sellings = \App\Models\WalletTransaction::selectRaw(\DB::raw('CONCAT("selling_", `id`) as id, updated_at as date, "selling" as type, amount, seller_account_id as user_id'));



        if ($filters = $request->input('filters', false)) {

            foreach ($filters as $ind => $filter) {

                $filter = (object) $filter;
                if ($filter->id == 'type') {
                    $transactions = ${$filter->value};
                    unset($filters[$ind]);
                }
            }

            if (!isset($transactions)) $transactions = $charges->union($buyings)->union($sellings);



            $transactions = \DB::table( \DB::raw("({$transactions->toSql()}) as `data`") )
            ->mergeBindings($transactions->getQuery());

            foreach ($filters as $filter) {
                $filter = (object) $filter;
                if (($filter->from_to)) {

                    $filter->value = (object) $filter->value;

                    if (isset($filter->value->from) && isset($filter->value->to)) {

                        $transactions = $transactions->whereBetween($filter->id, [$filter->value->from, $filter->value->to]);
                    }
                    elseif (isset($filter->value->from)) {
                        $transactions = $transactions->where($filter->id, '>=',  $filter->value->from);
                    }
                    elseif (isset($filter->value->to)) {
                        $transactions = $transactions->where($filter->id, '<=',  $filter->value->to);
                    }
                }
                elseif ($filter->type == 'select'){
                    $transactions = $transactions->where($filter->id, $filter->value);
                }
                else {
                    $transactions = $transactions->where($filter->id, 'LIKE',  '%'.$filter->value.'%');
                }
            }
        }

        if (!isset($transactions)) $transactions = $charges->union($buyings)->union($sellings);

        $transactions = $transactions->orderBy('date', 'DESC')->paginate($request->input('per_page', 10));

        return response()->json($transactions);
    }

    public function queue(Request $request){
        /*
        Return info about not processed transactions
        */
    }
}
