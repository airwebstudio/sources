<?php

namespace App\Http\Controllers;

use App\Models\SellerReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SellerReviewController extends Controller
{
    /**
     * @OA\Put (
     *     tags={"user/seller/review"},
     *     path="/api/user/seller/review/{seller_user_id}",
     *     summary="Add Seller review",
     *
     *     @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"review"},
     *              @OA\Property(property="review", type="string", format="review", example="review")
     *          ),
     *      ),
     *
     *     @OA\Response( response=200, description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Seller review successfully added!")
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
            return response()->json(['message' => 'You can\'t review yourself']);
        }
        if(!$seller->seller){
            return response()->json(['message' => 'User not a seller!']);
        }
        $review = request('review');

        if(SellerReview::where(['user_id'=>$user->id, 'seller_id'=>$seller->id])->exists()) {
            return response()->json(['message' => 'You have already added a review to this Seller']);
        }

        $newSellerReview = new SellerReview();
        $newSellerReview->user_id = $user->id;
        $newSellerReview->seller_id = $seller->id;
        $newSellerReview->review = $review;
        $newSellerReview->save();

        return response()->json(['message' => 'Seller review successfully added!']);
    }


    /**
     * @OA\Get(
     *     tags={"user/seller/review"},
     *     path="/api/user/seller/review/{seller_user_id}",
     *     summary="get Seller review list",
     *
     *     @OA\Response(response=200, description="", @OA\JsonContent(ref="#/components/schemas/SellerReview"))
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

        return response()->json($seller->seller->review_list);
    }
}
