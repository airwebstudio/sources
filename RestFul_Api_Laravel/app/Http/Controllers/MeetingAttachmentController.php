<?php

namespace App\Http\Controllers;

use App\Journal\Journal;
use App\Models\Meeting;
use App\Models\StorageFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class MeetingAttachmentController extends Controller
{
    protected const MEETING_ATTACHMENT_PATH = 'attachments';

    /**
     * @OA\Post(
     *     tags={"meeting/attachments"},
     *     path="/api/meeting/attachments/{meeting_hash}",
     *     summary="Upload Attachments to Meeting",
     *
     *     @OA\RequestBody(
     *          required=true,
     *          description="uploads",
     *          @OA\JsonContent(
     *              required={"uploads"},
     *              @OA\Property(property="uploads", type="string", format="uploads", example=""),
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
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request, $meeting_hash)
    {
        $user = auth()->user();
        if(!$user){
            return response()->json(['error' => 'User not found']);
        }

        $meeting = Meeting::where([
            'hash' => $meeting_hash
        ])->first();
        if(!$meeting){
            return response()->json(['error' => 'Meeting not found']);
        }

        $StorageFileController = new StorageFileController;
        $return = $StorageFileController->do_upload($request, self::MEETING_ATTACHMENT_PATH.'/'.$meeting_hash.'/'.$user->id, $meeting_hash);
        $meeting->attachments()->attach($return['storage_id'],['user_id' => $user->id]);

        $Journal = new Journal;
        $Journal->add_event([
            'primary_id'=> $user->id,
            'secondary_id'=> $meeting->id,
            'type'=> 'meeting_attachments_upload',
            'description'=> 'Upload Attachments to Meeting',
            'details'=> json_encode($return),
        ]);

        return response()->json($return);
    }

    /**
     * @OA\Get(
     *     tags={"meeting/attachments"},
     *     path="/api/meeting/attachments/{meeting_hash}/{unique_key}",
     *     summary="Get Meeting Attachment",
     *
     *     @OA\RequestBody(
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="meeting_hash", type="integer", format="meeting_hash", example="dd828553-075e-4204-a7fb-ae6896f8f7e1"),
     *              @OA\Property(property="unique_key", type="integer", format="unique_key", example="gD0UrGynsaMuvxGdpSvfv0qr1i37tde8XZedkHCy.png"),
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
     * @param $meeting_hash
     * @param $unique_key
     * @return \Illuminate\Http\JsonResponse
     */
    public function read($meeting_hash, $unique_key)
    {
        $meeting = Meeting::where([
            'hash' => $meeting_hash
        ])->first();
        if(!$meeting){
            return response()->json(['error' => 'Meeting not found']);
        }

        return response()->json($meeting->attachments->where('unique_key', $unique_key)->first());
    }

    /**
     * @OA\Get(
     *     tags={"meeting/attachments"},
     *     path="/api/meeting/attachments/{meeting_hash}",
     *     summary="Get Meeting Attachments list",
     *
     *     @OA\RequestBody(
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="meeting_hash", type="integer", format="meeting_hash", example="dd828553-075e-4204-a7fb-ae6896f8f7e1"),
     *          ),
     *     ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="response",
     *          @OA\JsonContent(
     *              @OA\Property(property="meeting_hash", type="array", @OA\Items(ref="#/components/schemas/StorageFile")),
     *          )
     *      )
     * )
     *
     * @param $meeting_hash
     * @param $unique_key
     * @return \Illuminate\Http\JsonResponse
     */
    public function list($meeting_hash)
    {
        $user = auth()->user();
        if(!$user){
            return response()->json(['error' => 'User not found']);
        }

        $meeting = Meeting::where([
            'hash' => $meeting_hash
        ])->first();
        if(!$meeting){
            return response()->json(['error' => 'Meeting not found']);
        }

        return response()->json($meeting->attachments);
    }

    /**
     * @OA\Delete (
     *     tags={"meeting/attachments"},
     *     path="/api/meeting/attachments/{meeting_hash}/{unique_key}",
     *     summary="Delete Meeting Attachment",
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
     * @param $meeting_hash
     * @param $unique_key
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($meeting_hash, $unique_key)
    {
        $user = auth()->user();
        if(!$user){
            return response()->json(['error' => 'User not found']);
        }

        $meeting = Meeting::where([
            'hash' => $meeting_hash
        ])->first();
        if(!$meeting){
            return response()->json(['error' => 'Meeting not found']);
        }

        $attachment = $meeting->attachments->where('unique_key', $unique_key)->first();
        if(!$attachment){
            return response()->json(['error' => 'Attachment not found'], 404);
        }
        if($attachment->delete_storage()){

            $Journal = new Journal;
            $Journal->add_event([
                'primary_id'=> $user->id,
                'secondary_id'=> $meeting->id,
                'type'=> 'meeting_attachments_delete',
                'description'=> 'Delete Attachments from Meeting',
                'details'=> $meeting->toJson(),
            ]);

            return response()->json(['message' => 'Successfully deleted!']);
        }else{
            return response()->json(['error' => 'Error']);
        }
    }

}
