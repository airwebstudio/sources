<?php

namespace App\Http\Controllers;

use App\Models\StorageFile;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class StorageFileController extends Controller
{
    /**
     * Create a new SellerController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['create']]);
    }

    /**
     * @OA\Post(
     *     tags={"storage"},
     *     path="/api/storage/upload",
     *     summary="Upload file to Storage",
     *
     *     @OA\RequestBody(
     *          required=true,
     *          description="uploads",
     *          @OA\JsonContent(
     *              required={"uploads"},
     *              @OA\Property(property="uploads", type="string", format="title", example="title"),
     *          ),
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Successfully uploadet!"),
     *              @OA\Property(property="file", type="string", example="other/2021/04/02/0/WpIgOAOTzfhf7wbjtpsIv2nmp5MYZJ61owRkpY7i.png"),
     *              @OA\Property(property="key", type="string", example="WpIgOAOTzfhf7wbjtpsIv2nmp5MYZJ61owRkpY7i.png"),
     *              @OA\Property(property="storage_id", type="string", example="54")
     *          )
     *      )
     * )
     *
     * storage upload
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        return response()->json($this->do_upload($request));
    }

    /**
     * storage do_upload
     * @param Request $request
     * @param string $path
     * @param string $meeting_hash
     * @return string[]
     */
    public function do_upload(Request $request, $path = 'other', $meeting_hash = null){

//         $fileTypes = ['image/jpeg','image/png','video/mp4'];

        if (!$request->hasFile('uploads') ) {
            return [
                'error' => 'File not find',
                'status' => 400
            ];
        }

        $uploads = $request->uploads;

        $mimeType = $uploads->getMimeType();
//         if(!in_array($mimeType, $fileTypes, true)){
//             return [
//                 'error' => 'Disallowed File Type'
//             ];
//         }

        $user = auth()->user();
        $user_id_path = $user->id ?? 0;

        $full_path = $path.'/'.Carbon::now()->format('Y/m/d').'/'.$user_id_path;
        $file_path = $uploads->store($full_path);

        $storageFile = new StorageFile();
        $storageFile->name = $uploads->getClientOriginalName();
        $storageFile->user_id = $user->id ?? null;
        $storageFile->unique_key = $uploads->hashName();
        $storageFile->mime_type = $mimeType;
        $storageFile->filesize = $uploads->getSize();
        $storageFile->url_on_storage = $file_path;
        $storageFile->save();

        return [
            'message' => 'Successfully uploadet!',
            'file' => $file_path,
            'key' => $uploads->hashName(),
            'storage_id' => $storageFile->id,
        ];
    }

    /**
     * @OA\Get(
     *     tags={"storage"},
     *     path="/api/storage/{unique_key}",
     *     summary="Get file info by unique_key",
     *
     *     @OA\RequestBody(
     *          required=true,
     *          description="unique_key",
     *          @OA\JsonContent(
     *              required={"unique_key"},
     *              @OA\Property(property="unique_key", type="string", format="unique_key", example="Rb1kIpB7AyVaCPwTD2YT2S8lcZB3M49meIK2RWit.png"),
     *          ),
     *      ),
     *     @OA\Response(
     *          response=200,
     *          description="response",
     *          @OA\JsonContent(ref="#/components/schemas/StorageFile")
     *      )
     * )
     *
     * Get storage
     * @param $unique_key
     * @return \Illuminate\Http\JsonResponse
     */
    public function read($unique_key)
    {
        $storageFile = new StorageFile();
        $storageFile = $storageFile->where('unique_key', $unique_key)->first();

        if(!$storageFile){
            return response()->json(['error' => 'Media not exist']);
        }

        return response()->json($storageFile);
    }

    /**
     * @OA\Delete (
     *     tags={"storage"},
     *     path="/api/storage/{unique_key}",
     *     summary="Delete file from Storage by unique_key",
     *
     *     @OA\RequestBody(
     *          required=true,
     *          description="unique_key",
     *          @OA\JsonContent(
     *              required={"unique_key"},
     *              @OA\Property(property="unique_key", type="string", format="unique_key", example="XSEa1ZCPOSKxTLwJVTINaV2nN9Ng9CCS12stnpC4.png"),
     *          ),
     *      ),
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
     * @param $unique_key
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($unique_key)
    {
        $storageFile = new StorageFile();
        $storageFile = $storageFile->where('unique_key', $unique_key)->first();

        if(!$storageFile){
            return response()->json(['error' => 'Storage file not exist']);
        }

        if($storageFile->delete() && Storage::delete($storageFile->url_on_storage)){
            return response()->json(['message' => 'Successfully deleted!']);
        }else{
            return response()->json(['error' => 'Error']);
        }
    }


}
