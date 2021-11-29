<?php

namespace App\Http\Controllers;

use App\Models\UserFeedItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserFeedItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['read', 'feed_items_public']]);
    }
    /**
     * @OA\Put (
     *     tags={"user/feed-item"},
     *     path="/api/user/feed-item",
     *     summary="Create UserFeedItem",
     *
     *     @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"title", "description", "is_active"},
     *              @OA\Property(property="title", type="string", format="title", example="title"),
     *              @OA\Property(property="description", type="string", format="description", example="description"),
     *              @OA\Property(property="is_active", type="string", format="is_active", example="1")
     *          ),
     *      ),
     *
     *     @OA\Response( response=200, description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="User Feed Item successfully created!"),
     *              @OA\Property(property="UserFeedItem", type="object", ref="#/components/schemas/UserFeedItem")
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

        $title = request('title');
        $description = request('description');
        $is_active = request('is_active');

        $UserFeedItem = new UserFeedItem();
        $UserFeedItem->user_id = $user->id;
        $UserFeedItem->title = $title;
        $UserFeedItem->description = $description;
        $UserFeedItem->is_active = $is_active;
        $UserFeedItem->save();

        return response()->json([
            'message' => 'User Feed Item successfully created!',
            'UserFeedItem' => $UserFeedItem
        ]);

    }

    /**
     * @OA\Get(
     *     tags={"user/feed-item"},
     *     path="/api/user/feed-item/{id}",
     *     summary="Get UserFeedItem",
     *
     *     @OA\Response(response=200, description="", @OA\JsonContent(ref="#/components/schemas/UserFeedItem"))
     * )
     *
     * @return JsonResponse
     */
    public function read($id)
    {
        $user = auth()->user();

        if(!$user){
            return response()->json(['message' => 'User not found!']);
        }

        $UserFeedItem = UserFeedItem::find($id);
        if($UserFeedItem->user_id != $user->id){
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        return response()->json($UserFeedItem);
    }

    /**
     * @OA\Post(
     *     tags={"user/feed-item"},
     *     path="/api/user/feed-item/{id}",
     *     summary="Update UserFeedItem",
     *
     *     @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="title", type="string", format="title", example="title"),
     *              @OA\Property(property="description", type="string", format="description", example="description"),
     *              @OA\Property(property="is_active", type="string", format="is_active", example="1"),
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
     * @return JsonResponse
     */
    public function update($id)
    {
        $user = auth()->user();

        if(!$user){
            return response()->json(['message' => 'User not found!']);
        }

        $UserFeedItem = UserFeedItem::find($id);
        if($UserFeedItem->user_id != $user->id){
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $title = request('title');
        $description = request('description');
        $is_active = request('is_active');

        $UserFeedItem->title = $title ?: $UserFeedItem->title;
        $UserFeedItem->description = $description ?: $UserFeedItem->description;
        $UserFeedItem->is_active = isset($is_active) ? $is_active : $UserFeedItem->is_active;
        $UserFeedItem->save();

        return response()->json(['message' => 'Successfully updatet!']);

    }

    /**
     * @OA\Delete (
     *     tags={"user/feed-item"},
     *     path="/api/user/feed-item/{id}",
     *     summary="Delete UserFeedItem",
     *
     *     @OA\Response(
     *          response=200,
     *          description="response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Successfully deleted!")
     *          )
     *      )
     * )
     *
     * @return JsonResponse
     */
    public function delete($id)
    {
        $user = auth()->user();

        if(!$user){
            return response()->json(['message' => 'User not found!']);
        }

        $UserFeedItem = UserFeedItem::find($id);
        if($UserFeedItem->user_id != $user->id){
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $UserFeedItem->delete();

        return response()->json(['message' => 'Successfully deleted!']);

    }

    /**
     * @OA\Get(
     *     tags={"user/feed-item"},
     *     path="/api/user/feed-items",
     *     summary="Get UserFeedItems for current user",
     *
     *     @OA\RequestBody(
     *          description="search",
     *          @OA\JsonContent(
     *              @OA\Property(property="page", type="string", example="1"),
     *              @OA\Property(property="per_page", type="string", format="per_page", example="1"),
     *          ),
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="current_page", type="string", format="current_page", example="1"),
     *              @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/UserFeedItem")),
     *              @OA\Property(property="first_page_url", type="string", format="first_page_url", example="http://api-public.timepal.local/api/user/feed-items?page=1"),
     *              @OA\Property(property="from", type="string", format="from", example="1"),
     *              @OA\Property(property="last_page", type="string", format="last_page", example="2"),
     *              @OA\Property(property="last_page_url", type="string", format="last_page_url", example="http://api-public.timepal.local/api/user/feed-items?page=2"),
     *              @OA\Property(property="links", type="array",
     *                  example={
     *                  {
     *                      "url": null,
     *                      "label": "&laquo; Previous",
     *                      "active": false
     *                  },
     *                  {
     *                      "url": "http://api-public.timepal.local/api/user/feed-items?page=1",
     *                      "label": "1",
     *                      "active": true
     *                  },
     *                  {
     *                      "url": "http://api-public.timepal.local/api/user/feed-items?page=2",
     *                      "label": "2",
     *                      "active": false
     *                  },
     *                  {
     *                      "url": "http://api-public.timepal.local/api/user/feed-items?page=2",
     *                      "label": "Next &raquo;",
     *                      "active": false
     *                  }},
     *                  @OA\Items(
     *                      @OA\Property(property="url", type="string", format="url"),
     *                      @OA\Property(property="label", type="string", format="label"),
     *                      @OA\Property(property="active", type="string", format="active")
     *                  ),
     *              ),
     *              @OA\Property(property="next_page_url", type="string", format="next_page_url", example="http://api-public.timepal.local/api/user/feed-items?page=2"),
     *              @OA\Property(property="path", type="string", format="path", example="http://api-public.timepal.local/api/user/feed-items"),
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
    public function feed_items_for_current_user()
    {
        $per_page = request('per_page') ?: 10;
        $user = auth()->user();

        if(!$user){
            return response()->json(['message' => 'User not found!']);
        }

        $UserFeedItems = UserFeedItem::where('user_id', $user->id)->paginate($per_page);

        return $UserFeedItems;
    }

    /**
     * @OA\Get(
     *     tags={"user/feed-item"},
     *     path="/api/public/feed-items",
     *     summary="Get UserFeedItems",
     *
     *     @OA\RequestBody(
     *          description="search",
     *          @OA\JsonContent(
     *              @OA\Property(property="user_id", type="string", example="1"),
     *              @OA\Property(property="page", type="string", example="1"),
     *              @OA\Property(property="per_page", type="string", format="per_page", example="1"),
     *          ),
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="current_page", type="string", format="current_page", example="1"),
     *              @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/UserFeedItem")),
     *              @OA\Property(property="first_page_url", type="string", format="first_page_url", example="http://api-public.timepal.local/api/public/feed-items?page=1"),
     *              @OA\Property(property="from", type="string", format="from", example="1"),
     *              @OA\Property(property="last_page", type="string", format="last_page", example="2"),
     *              @OA\Property(property="last_page_url", type="string", format="last_page_url", example="http://api-public.timepal.local/api/public/feed-items?page=2"),
     *              @OA\Property(property="links", type="array",
     *                  example={
     *                  {
     *                      "url": null,
     *                      "label": "&laquo; Previous",
     *                      "active": false
     *                  },
     *                  {
     *                      "url": "http://api-public.timepal.local/api/public/feed-items?page=1",
     *                      "label": "1",
     *                      "active": true
     *                  },
     *                  {
     *                      "url": "http://api-public.timepal.local/api/public/feed-items?page=2",
     *                      "label": "2",
     *                      "active": false
     *                  },
     *                  {
     *                      "url": "http://api-public.timepal.local/api/public/feed-items?page=2",
     *                      "label": "Next &raquo;",
     *                      "active": false
     *                  }},
     *                  @OA\Items(
     *                      @OA\Property(property="url", type="string", format="url"),
     *                      @OA\Property(property="label", type="string", format="label"),
     *                      @OA\Property(property="active", type="string", format="active")
     *                  ),
     *              ),
     *              @OA\Property(property="next_page_url", type="string", format="next_page_url", example="http://api-public.timepal.local/api/public/feed-items?page=2"),
     *              @OA\Property(property="path", type="string", format="path", example="http://api-public.timepal.local/api/public/feed-items"),
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
    public function feed_items_public(Request $request)
    {
        $per_page = request('per_page') ?: 10;
        $seller_id = request('user_id');
        $where = [];

        if($seller_id){
            $where['user_id'] = $seller_id;
        }

        return UserFeedItem::where($where)->paginate($per_page);
    }

}
