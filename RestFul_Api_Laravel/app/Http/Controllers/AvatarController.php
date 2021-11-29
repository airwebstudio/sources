<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AvatarController extends Controller
{
    protected const AVATAR_PATH = 'avatar';

    /**
     * Create a new SellerController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['read']]);
    }

    /**
     * @OA\Post(
     *     tags={"avatar"},
     *     path="/api/avatar/upload",
     *     summary="Upload avatar to current user",
     *
     *     @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"uploads"},
     *              @OA\Property(property="uploads", type="file", format="uploads", example=""),
     *          ),
     *      ),
     *
     *      @OA\Response(response=200, description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Successfully uploadet!"),
     *              @OA\Property(property="file", type="string", example="other/2021/04/02/7/AinLM8fReovje5Su35JcCnDsAyC5wr1Czb2vfCs0.png"),
     *              @OA\Property(property="key", type="string", example="AinLM8fReovje5Su35JcCnDsAyC5wr1Czb2vfCs0.png"),
     *              @OA\Property(property="storage_id", type="string", example="51")
     *          )
     *      ),
     *
     *      @OA\Response(response=401, description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthenticated.")
     *          )
     *      ),
     * )
     *
     * avatar upload / change
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        $StorageFileController = new StorageFileController;
        $return = $StorageFileController->do_upload($request, self::AVATAR_PATH);

        if(isset($return['storage_id']) && $return['storage_id']){
            $user = auth()->user();
            $old_avatar = $user->avatar;
            $user->avatar_id = $return['storage_id'];
            $user->avatar_path = '/'.$return['file'];
            $user->save();

            if($old_avatar) {
                $old_avatar->delete_storage();
            }
        }

        return response()->json($return);
    }

    /**
     * @OA\Get(
     *     tags={"avatar"},
     *     path="/api/avatar/{user_id}",
     *     summary="Get avatar of current user or by user_id",
     *
     *     @OA\RequestBody(
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="user_id", type="integer", format="user_id", example="1"),
     *          ),
     *     ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="response",
     *          @OA\JsonContent(ref="#/components/schemas/StorageFile")
     *      )
     * )
     *
     * Get avatar of current user or by user_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function read()
    {
        if(request('user_id')){
            $user = new User();
            $user = $user->find(request('user_id'));
        }else{
            $user = auth()->user();
        }

        if(!$user){
            return response()->json(['error' => 'User not found']);
        }

        if(!$user->avatar){
            return response()->json(['error' => 'User does not have an avatar']);
        }

        return response()->json($user->avatar);
    }

    /**
     * @OA\Delete (
     *     tags={"avatar"},
     *     path="/api/avatar",
     *     summary="Delete avatar of current user",
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
     * storage Delete
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete()
    {
        $user = auth()->user();

        $delete_avatar = $user->avatar;

        if(!$delete_avatar){
            return response()->json(['error' => 'User does not have an avatar']);
        }

        $user->avatar_id = null;
        $user->save();

        if($delete_avatar->delete_storage()){
            return response()->json(['message' => 'Successfully deleted!']);
        }else{
            return response()->json(['error' => 'Error']);
        }

    }

}
