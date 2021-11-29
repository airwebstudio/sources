<?php

namespace App\Http\Controllers;

use App\Journal\Journal;
use App\Models\Meeting;
use App\Models\MeetingAvailability;
use App\Models\MeetingAvailabilityParticipant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MeetingAvailabilityParticipantController extends Controller
{
    /**
     * @OA\Put (
     *     tags={"meeting_availability"},
     *     path="/api/buyer/availability/{availability_id}",
     *     summary="Add user to Meeting Availability",
     *
     *     @OA\RequestBody(
     *          required=true,
     *          description="availability_id",
     *          @OA\JsonContent(
     *              @OA\Property(property="availability_id", type="string", format="availability_id", example="1"),
     *          ),
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="response",
     *           @OA\JsonContent(
     *               @OA\Property(property="message", type="string", example="Successfully proposed!")
     *          )
     *      )
     * )
     *
     * @param $availability_id
     * @return JsonResponse
     */
    public function add_user_to_availability_meeting($availability_id): JsonResponse
    {
        $user = auth()->user();
        if(!$user) {
            return response()->json(['error' => 'Current user not found'], 404);
        }

        $MeetingAvailability = MeetingAvailability::find($availability_id);
        if(!$MeetingAvailability){
            return response()->json(['error' => 'Meeting Availability not found'], 404);
        }

        $participants_count = $MeetingAvailability->participants->where('user_id', $user->id)->count();
        if($participants_count){
            return response()->json(['error' => 'User already proposed.'], 403);
        }

        $Participant = $MeetingAvailability->addParticipant($user->id);

        $Journal = new Journal;
        $Journal->add_event([
            'primary_id'=> $user->id,
            'secondary_id'=> $MeetingAvailability->id,
            'type'=> 'add_user_to_availability_meeting',
            'description'=> 'Add user to Meeting Availability',
            'details'=> $Participant->toJson(),
        ]);

        return response()->json(['message' => 'Successfully proposed!']);

    }

    /**
     * @OA\Get (
     *     tags={"meeting_availability"},
     *     path="/api/seller/availability/{id}/participants",
     *     summary="Get all proposed users for Meeting Availability",
     *
     *     @OA\RequestBody(
     *          required=true,
     *          description="availability id",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="string", format="id", example="1"),
     *          ),
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="response",
     *           @OA\JsonContent(
     *               @OA\Property(property="users", type="array", @OA\Items(ref="#/components/schemas/MeetingAvailabilityParticipant"))
     *          )
     *      )
     * )
     *
     * @param $id
     * @return JsonResponse
     */
    public function get_users_list($id): JsonResponse
    {
        $MeetingAvailability = MeetingAvailability::find($id);
        if(!$MeetingAvailability){
            return response()->json(['error' => 'Meeting Availability not found'], 404);
        }

        if(!$MeetingAvailability->participants->count()){
            return response()->json(['error' => 'Users not found'], 403);
        }
        return response()->json(['users' => $MeetingAvailability->participants]);

    }

    /**
     * @OA\Post  (
     *     tags={"meeting_availability"},
     *     path="/api/seller/availability/participant/{id}",
     *     summary="Approve proposed user for Meeting Availability",
     *
     *     @OA\RequestBody(
     *          required=true,
     *          description="proposed id",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="string", format="id", example="1"),
     *          ),
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="response",
     *           @OA\JsonContent(
     *               @OA\Property(property="message", type="string", example="Successfully approved!")
     *          )
     *      )
     * )
     *
     * @param $id
     * @return JsonResponse
     */
    public function approve($id): JsonResponse
    {
        $MeetingAvailabilityParticipant = MeetingAvailabilityParticipant::find($id);
        if(!$MeetingAvailabilityParticipant){
            return response()->json(['error' => 'Meeting Availability Participant not found'], 404);
        }

        $meeting = $MeetingAvailabilityParticipant->meeting;
        if(!$meeting) {

            $meeting = Meeting::create([
                'hash' => (string)Str::uuid(),
                'user_id' => $MeetingAvailabilityParticipant->meeting_availability->user_id,
                'name' => $MeetingAvailabilityParticipant->meeting_availability->name,
                'description' => $MeetingAvailabilityParticipant->meeting_availability->description,
                'starting_at' => $MeetingAvailabilityParticipant->meeting_availability->starting_at,
            ]);

            if ($MeetingAvailabilityParticipant->meeting_availability->participants) {
                foreach ($MeetingAvailabilityParticipant->meeting_availability->participants as $participant) {
                    $participant->meeting_availability_id = null;
                    $participant->meeting_id = $meeting->id;
                    $participant->save();
                }
            }
        }

        $Participant = $meeting->addParticipant($MeetingAvailabilityParticipant->user_id);
        $MeetingAvailabilityParticipant->delete();
        $MeetingAvailabilityParticipant->meeting_availability->user->seller->calculate_meetings_participants_count();

        $Journal = new Journal;
        $Journal->add_event([
            'primary_id'=> $MeetingAvailabilityParticipant->user_id,
            'secondary_id'=> $meeting->id,
            'type'=> 'approve_add_user_to_availability_meeting',
            'description'=> 'Approve add user to Meeting Availability',
            'details'=> $Participant->toJson(),
        ]);

        return response()->json(['message' => 'Successfully approved!']);

    }

    /**
     * @OA\Delete (
     *     tags={"meeting_availability"},
     *     path="/api/seller/availability/participant/{id}",
     *     summary="Delete proposed user for Meeting Availability",
     *
     *     @OA\RequestBody(
     *          required=true,
     *          description="proposed id",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="string", format="id", example="1"),
     *          ),
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="response",
     *           @OA\JsonContent(
     *               @OA\Property(property="message", type="string", example="Successfully deleteed!")
     *          )
     *      )
     * )
     *
     * @param $id
     * @return JsonResponse
     */
    public function delete($id): JsonResponse
    {
        $MeetingAvailabilityParticipant = MeetingAvailabilityParticipant::find($id);
        if(!$MeetingAvailabilityParticipant){
            return response()->json(['error' => 'Participant not found'], 404);
        }

        $Journal = new Journal;
        $Journal->add_event([
            'primary_id'=> $MeetingAvailabilityParticipant->user_id,
            'secondary_id'=> $MeetingAvailabilityParticipant->id,
            'type'=> 'delete_user_from_availability_meeting',
            'description'=> 'Delete user froom Meeting Availability',
            'details'=> $MeetingAvailabilityParticipant->toJson(),
        ]);

        $MeetingAvailabilityParticipant->delete();

        return response()->json(['message' => 'Successfully deleteed!']);

    }

}
