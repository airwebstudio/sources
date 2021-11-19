<?php

namespace App\PayPal;

use PayPalCheckoutSdk\Orders\OrdersCreateRequest;

class Payment
{

    public static function make($transaction){
        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');
        $request->body = [
            'intent' => 'CAPTURE',
            'application_context' => [
                'return_url' => getenv('PUBLIC_APP_URL').'/paypal/success',
                'cancel_url' => getenv('PUBLIC_APP_URL').'/paypal/cancel'
            ],
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => $transaction->currency,
                        'value' => $transaction->amount
                    ]
                ]
            ]
        ];
        $client = Client::client($transaction->credentials);
        $result = $client->execute($request);

        return $result;
    }
}
