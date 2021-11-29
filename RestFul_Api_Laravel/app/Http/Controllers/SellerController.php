<?php

namespace App\Http\Controllers;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SellerController extends Controller
{
    /**
     * Create a new SellerController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'info', 'find']]);
    }

    /**
     * @OA\Put (
     *     tags={"user/seller"},
     *     path="/api/user/seller",
     *     summary="Create Seller settings",
     *
     *     @OA\RequestBody(
     *          required=true,
     *          description="name, description, price, payment_system, credentials",
     *          @OA\JsonContent(
     *              required={"name", "description", "price", "payment_system", "credentials"},
     *              @OA\Property(property="name", type="string", format="name", example=""),
     *              @OA\Property(property="description", type="string", format="description", example=""),
     *              @OA\Property(property="price", type="string", format="price", example=""),
     *              @OA\Property(property="payment_system", type="string", format="payment_system", example="PayPal"),
     *              @OA\Property(property="credentials", type="json", format="credentials", example=""),
     *              @OA\Property(property="is_private", type="string", format="is_private", example="0"),
     *          ),
     *      ),
     *
     *     @OA\Response( response=200, description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Seller settings successfully created!")
     *          )
     *      )
     * )
     *
     * Create Seller settings
     */
    public function create()
    {
        $user = auth()->user();

        if(!$user){
            return response()->json(['message' => 'User not found!']);
        }

        $name = request('name');
        $description = request('description');
        $price = request('price');
        //$payment_system = request('payment_system');
        //$credentials = request('credentials');

        $seller = new Seller();
        $seller->name = $name;
        $seller->description = $description;
        $seller->price = $price;
        $seller->payment_system = '';
        $seller->credentials = [];
        $seller->is_private = request('is_private') ?: 0;

        if(!$user->seller) {
            $user->seller()->save($seller);
            $user->is_seller = true;
            $user->save();
            return response()->json(['message' => 'Seller settings successfully created!']);
        }

        return response()->json(['message' => 'Seller settings already exist!']);
    }

    /**
     * @OA\Get(
     *     tags={"user/seller"},
     *     path="/api/user/seller",
     *     summary="Get Seller settings",
     *
     *     @OA\Response(response=200, description="", @OA\JsonContent(ref="#/components/schemas/Seller"))
     * )
     *
     * Read seller
     *
     * @return JsonResponse
     */
    public function read()
    {
        $user = auth()->user();

        if(!$user){
            return response()->json(['message' => 'User not found!']);
        }

        return response()->json($user->seller);
    }


    /**
     * @OA\Get(
     *     tags={"user/seller"},
     *     path="/api/user/seller/info/{id}",
     *     summary="Get Seller settings by User ID",
     *
     *     @OA\Response(response=200, description="", @OA\JsonContent(ref="#/components/schemas/Seller"))
     * )
     *
     * Read seller
     *
     * @return JsonResponse
     */
    public function info($id)
    {
        $seller = Seller::where('user_id', $id)->first();

        return response()->json($seller);
    }

    /**
     * @OA\Post(
     *     tags={"user/seller"},
     *     path="/api/user/seller",
     *     summary="Update seller settings",
     *
     *     @OA\RequestBody(
     *          required=true,
     *          description="name, description, price, payment_system, credentials",
     *          @OA\JsonContent(
     *              @OA\Property(property="user_id", type="string", format="user_id", example="1"),
     *              @OA\Property(property="name", type="string", format="name", example=""),
     *              @OA\Property(property="description", type="string", format="description", example=""),
     *              @OA\Property(property="price", type="string", format="price", example=""),
     *              @OA\Property(property="payment_system", type="string", format="payment_system", example="PayPal"),
     *              @OA\Property(property="credentials", type="json", format="credentials", example=""),
     *              @OA\Property(property="is_private", type="string", format="is_private", example="0"),
     *          ),
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="response",
     *           @OA\JsonContent(
     *               @OA\Property(property="message", type="string", example="Successfully updatet!")
     *          )
     *      )
     * )
     *
     * Update seller
     *
     * @return JsonResponse
     */
    public function update()
    {

        $name = request('name');
        $description = request('description');
        $price = request('price');
        $payment_system = request('payment_system');
        $credentials = request('credentials');
        $is_private = request('is_private');

        $user = auth()->user();

        if(!$user){
            return response()->json(['message' => 'User not found!']);
        }

        if($user->seller) {

            $user->seller->name = $name ?: $user->seller->name;
            $user->seller->description = $description ?: $user->seller->description;
            $user->seller->price = $price ?: $user->seller->price;
            $user->seller->payment_system = $payment_system ?: $user->seller->payment_system;
            $user->seller->credentials = $credentials ?: $user->seller->credentials;
            $user->seller->is_private = $is_private ?? $user->seller->is_private;
            $user->seller()->save($user->seller);

        } else {

            $seller = new Seller();
            $seller->name = $name;
            $seller->description = $description;
            $seller->price = $price;
            $seller->payment_system = $payment_system;
            $seller->credentials = $credentials;
            $seller->is_private = $is_private;

            $user->seller()->save($seller);
        }

        return response()->json(['message' => 'Seller settings successfully updated!']);

    }

    /**
     * @OA\Delete (
     *     tags={"user/seller"},
     *     path="/api/user/seller",
     *     summary="Delete seller settings",
     *
     *     @OA\Response(
     *          response=200,
     *          description="response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Seller settings successfully deleted!")
     *          )
     *      )
     * )
     *
     * Delete seller settings
     *
     * @return JsonResponse
     */
    public function delete()
    {

        $user = auth()->user();

        if(!$user){
            return response()->json(['message' => 'User not found!']);
        }

        if($user->seller) {
            $user->seller()->delete();
        }

        return response()->json(['message' => 'Seller settings successfully deleted!']);

    }

    /**
     * @OA\Get(
     *     tags={"user/seller"},
     *     path="/api/user/seller/find",
     *     summary="Find Users with Seller settings",
     *
     *     @OA\RequestBody(
     *          required=true,
     *          description="search",
     *          @OA\JsonContent(
     *              required={"s"},
     *              @OA\Property(property="s", type="string", example="test"),
     *              @OA\Property(property="page", type="string", example="1"),
     *              @OA\Property(property="per_page", type="string", format="per_page", example="1"),
     *          ),
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="response",
     *          @OA\JsonContent(
     *              @OA\Property(property="current_page", type="string", format="current_page", example="1"),
     *              @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User")),
     *              @OA\Property(property="first_page_url", type="string", format="first_page_url", example="http://api-public.timepal.local/api/user/seller/find?page=1"),
     *              @OA\Property(property="from", type="string", format="from", example="1"),
     *              @OA\Property(property="last_page", type="string", format="last_page", example="2"),
     *              @OA\Property(property="last_page_url", type="string", format="last_page_url", example="http://api-public.timepal.local/api/user/seller/find?page=2"),
     *              @OA\Property(property="links", type="array",
     *                  example={
     *                  {
     *                      "url": null,
     *                      "label": "&laquo; Previous",
     *                      "active": false
     *                  },
     *                  {
     *                      "url": "http://api-public.timepal.local/api/user/seller/find?page=1",
     *                      "label": "1",
     *                      "active": true
     *                  },
     *                  {
     *                      "url": "http://api-public.timepal.local/api/user/seller/find?page=2",
     *                      "label": "2",
     *                      "active": false
     *                  },
     *                  {
     *                      "url": "http://api-public.timepal.local/api/user/seller/find?page=2",
     *                      "label": "Next &raquo;",
     *                      "active": false
     *                  }},
     *                  @OA\Items(
     *                      @OA\Property(property="url", type="string", format="url"),
     *                      @OA\Property(property="label", type="string", format="label"),
     *                      @OA\Property(property="active", type="string", format="active")
     *                  ),
     *              ),
     *              @OA\Property(property="next_page_url", type="string", format="next_page_url", example="http://api-public.timepal.local/api/user/seller/find?page=2"),
     *              @OA\Property(property="path", type="string", format="path", example="http://api-public.timepal.local/api/user/seller/find"),
     *              @OA\Property(property="per_page", type="string", format="per_page", example="10"),
     *              @OA\Property(property="prev_page_url", type="string", format="prev_page_url", example="null"),
     *              @OA\Property(property="to", type="string", format="to", example="10"),
     *              @OA\Property(property="total", type="string", format="total", example="12"),
     *          )
     *      )
     * )
     *
     * Read seller
     *
     * @return JsonResponse
     */
    public function find()
    {
        $find = request('s');
        $per_page = request('per_page') ?: 10;

        $user = new User();

        $search = $user->where(function ($query) use ($find) {
            $query->where('name', 'like', '%'.$find.'%');
        })->whereHas('seller', function($query){
            $query->where('is_private','=', 0);
        })->where([
            ['is_seller', '=', true]
            ])->with(['seller'])->paginate($per_page);

        if(empty($search)){
            return response()->json(['message' => 'User not found!']);
        }
        return $search;
    }
}
