<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use Throwable;

class ChatsController extends Controller
{
    /**
     * Create a new ChatsController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
    }


    /**
     * @OA\Get(
     *     tags={"chat"},
     *     path="/api/chats/room/{room_id}",
     *     summary="Get chat by room id",
     *
     *      @OA\Response(response=200, description="response",
     *          @OA\JsonContent(
     *              @OA\Property(property="room", type="object", example="{roomID, users, messages}")
     *          )
     *      ),
     * )
     *
     * Get chat
     * @param $room_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function room($room_id)
    {
        if(!Redis::scard('room:'.$room_id) || !Redis::sismember('room:'.$room_id, auth()->user()->id)){
            return response()->json(['message' => 'Room ID: '.$room_id.' not found!']);
        }


        $messages = Redis::lrange('g_messages:'.$room_id, 0, -1);
        $objectMessages = [];
        foreach($messages as $message){
            $objectMessages[] = json_decode($message);
        }

        $users = array();
        $usersId = Redis::smembers("room:".$room_id);
        foreach($usersId as $userId){
            $users[] = Redis::hgetall('user:'.$userId);
        }

        $result = [
            "roomID" => $room_id,
            "users" => $users,
            "messages" => $objectMessages
        ];
        return response()->json($result);
    }

    /**
     * @OA\Get(
     *     tags={"chat"},
     *     path="/api/chats",
     *     summary="Get all chats",
     *
     *      @OA\Response(response=200, description="response",
     *          @OA\JsonContent(
     *              @OA\Property(property="rooms", type="array", @OA\Items(ref="#/components/schemas/User"), example="[{roomID, users, messages},..]")
     *          )
     *      ),
     * )
     *
     * Get list of chats
     * @return \Illuminate\Http\JsonResponse
     */
    public function list()
    {
        $result = array();

        $id = auth()->user()->id;
        $roomsId = Redis::smembers('user-rooms:'.$id);

        foreach($roomsId as $roomId){
            if(Redis::scard('room:'.$roomId) === 0){
                Redis::srem('user-rooms:'.$id, $roomId);
                continue;
            }

            $messages = Redis::lrange('g_messages:'.$roomId, 0, -1);
            $objectMessages = [];
            foreach($messages as $message){
                $objectMessages[] = json_decode($message);
            }

            $users = array();
            $usersId = Redis::smembers('room:'.$roomId);
            foreach($usersId as $userId){
                $users[] = Redis::hgetall('user:'.$userId);
            }

            $result[] = [
                "roomID" => $roomId,
                "users" => $users,
                "messages" => $objectMessages
            ];
        }
        return response()->json($result);
    }

    /**
     * @OA\Get(
     *     tags={"private chat"},
     *     path="/api/chats/private",
     *     summary="Get all private chats",
     *
     *      @OA\Response(response=200, description="response",
     *          @OA\JsonContent(
     *              @OA\Property(property="user_id", type="array", @OA\Items(ref="#/components/schemas/User"), example="[user_id]")
     *          )
     *      ),
     * )
     *
     * Get list of private chats
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_private_chats()
    {
        $id = auth()->user()->id;

        $list = array();
        $listTo = Redis::keys("private-chat:*:".$id.":messages");
        foreach($listTo as $item){
            preg_match('/private-chat:(.*):(.*):messages/', $item, $match);
            if($match[1] !== $match[2]){
                $list[] = $match[1];
            }
        }

        $listFrom = Redis::keys("private-chat:".$id.":*:messages");
        foreach($listFrom as $item){
            preg_match('/private-chat:.*:(.*):messages/', $item, $match);
            $list[] = $match[1];
        }

        return response()->json($list);
    }

}
