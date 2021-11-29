<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Seller;

class PayPalController extends Controller
{

    /**
     * @OA\Get (
     *     tags={"PayPal"},
     *     path="/api/paypal/update/{id}",
     *     summary="Update",
     *
     *     @OA\RequestBody(
     *          description="",
     *          @OA\JsonContent(
     *              required={"id"},
     *              @OA\Property(property="id", type="string", format="id", example=""),
     *          ),
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="response",
     *          @OA\JsonContent(
     *
     *          )
     *      )
     * )
     */
    public function update(Request $request, $id){
        $seller = Seller::first();

        if(getenv('FINANCE_API_USER')){
            $response = Http::withBasicAuth(getenv('FINANCE_API_USER'), getenv('FINANCE_API_PASS'))
                ->post(getenv("FINANCE_API_URL").'/api/paypal/update/'.$id, [
                    'credentials' => $seller->credentials
                ]);
        }else{
            $response = Http::post(getenv("FINANCE_API_URL").'/api/paypal/update/'.$id, [
                'credentials' => $seller->credentials
            ]);
        }

        //return response()->json(['result' => 'success', 'info' => 'rrrr']);
        return $response;
    }
}
