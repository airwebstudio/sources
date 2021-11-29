<?php

namespace App\Http\Controllers;

use App\Models\SellerRate;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SellerRateController extends Controller
{
    /**
     * @OA\Put (
     *     tags={"user/seller/rate"},
     *     path="/api/user/seller/rate/{seller_user_id}",
     *     summary="Add Seller rate",
     *
     *     @OA\RequestBody(
     *          required=true,
     *          description="rate (1...5)",
     *          @OA\JsonContent(
     *              required={"rate"},
     *              @OA\Property(property="rate", type="int", format="rate", example="1")
     *          ),
     *      ),
     *
     *     @OA\Response( response=200, description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Seller rate successfully added!")
     *          )
     *      )
     * )
     */
    public function create($seller_user_id)
    {
        $user = auth()->user();
        if(!$user){
            return response()->json(['message' => 'Current user not found!']);
        }
        $seller = User::find($seller_user_id);
        if(!$seller){
            return response()->json(['message' => 'Seller user not found!']);
        }
        if($seller->id == $user->id){
            return response()->json(['message' => 'You can\'t rate yourself']);
        }
        if(!$seller->seller){
            return response()->json(['message' => 'User not a seller!']);
        }
        $rate = request('rate');
        if(!in_array((int)$rate, range(1, 5), true)){
            return response()->json(['message' => 'Rate not in 1...5 Range!']);
        }

        if(SellerRate::where(['user_id'=>$user->id, 'seller_id'=>$seller->id])->exists()) {
            return response()->json(['message' => 'You have already added a rating to this Seller']);
        }

        $newSellerRate = new SellerRate();
        $newSellerRate->user_id = $user->id;
        $newSellerRate->seller_id = $seller->id;
        $newSellerRate->rate = $rate;
        $newSellerRate->save();

        $newSellerRate->seller_user->seller->calculate_avg_rate();

        return response()->json(['message' => 'Seller rate successfully added!']);
    }


    /**
     * @OA\Get(
     *     tags={"user/seller/rate"},
     *     path="/api/user/seller/rate/{seller_user_id}",
     *     summary="get Seller rate list",
     *
     *     @OA\Response(response=200, description="", @OA\JsonContent(ref="#/components/schemas/SellerRate"))
     * )
     *
     * @return JsonResponse
     */
    public function list($seller_user_id)
    {
        $seller = User::find($seller_user_id);
        if(!$seller){
            return response()->json(['message' => 'Seller user not found!']);
        }
        if(!$seller->seller){
            return response()->json(['message' => 'User not a seller!']);
        }

        return response()->json($seller->seller->rate_list);
    }

}
