<?php

namespace App\PayPal;

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;

class Client
{
    /**
     * Returns PayPal HTTP client instance with environment that has access
     * credentials context. Use this instance to invoke PayPal APIs, provided the
     * credentials have access.
     */
    public static function client($credentials)
    {
        $env = getenv("PAYPAL_MODE") == 'sandbox'
            ? new SandboxEnvironment($credentials['client_id'], $credentials['secret'])
            : new ProductionEnvironment($credentials['client_id'], $credentials['secret']);
        return new PayPalHttpClient($env);
    }

}
