<?php

namespace App\Http\Controllers;

use App\Journal\Journal;
use App\Models\MeetingAvailability;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeetingAvailabilityController extends Controller
{
    /**
     * @OA\Put(
     *     tags={"meeting_availability"},
     *     path="/api/seller/availability",
     *     summary="Create meeting availability",
     *
     *     @OA\RequestBody(
     *          required=true,
     *          description="type: 1:1 or 1:2 or 2:2",
     *          @OA\JsonContent(
     *              @OA\Property(property="name", type="string", format="name", example="name"),
     *              @OA\Property(property="description", type="string", format="description", example="some description"),
     *              @OA\Property(property="starting_at", type="string", format="date", example="2021-01-01 01:01:01"),
     *              @OA\Property(property="type", type="string", format="type", example="1:2", description="1:1 or 1:2 or 2:2"),
     *          ),
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="response",
     *           @OA\JsonContent(
     *               @OA\Property(property="message", type="string", example="Successfully createt!"),
     *               @OA\Property(property="meeting", type="object", ref="#/components/schemas/MeetingAvailability")
     *          )
     *      )
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $user = auth()->user();
        if(!$user) {
            return response()->json(['error' => 'Current user not found'], 404);
        }
        if(!$user->seller){
            return response()->json(['error' => 'Current user has no seller settings'], 406);
        }

        if(!in_array($request->input('type'), MeetingAvailability::MEETING_TYPE, true)){
            return response()->json(['error' => 'Meeting type not defined']);
        }

        $meeting = MeetingAvailability::create([
            'user_id' => $user->id,
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'type' => $request->input('type'),
            'starting_at' => $request->input('starting_at')
        ]);

        $Journal = new Journal;
        $Journal->add_event([
            'primary_id'=> $user->id,
            'secondary_id'=> $meeting->id,
            'type'=> 'meeting_availability_create',
            'description'=> 'Create meeting availability',
            'details'=> $meeting->toJson(),
        ]);

        return response()->json([
            'message' => 'Successfully created!',
            'meeting' => $meeting
        ]);
    }

    /**
     * @OA\Get(
     *     tags={"meeting_availability"},
     *     path="/api/seller/availability/{id}",
     *     summary="Get meeting availability info",
     *
     *     @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="string", format="id", example="1"),
     *          ),
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="response",
     *           @OA\JsonContent(
     *               @OA\Property(property="meeting", type="object", ref="#/components/schemas/MeetingAvailability")
     *          )
     *      )
     * )
     *
     *
     * @param $id
     * @return JsonResponse
     */
    public function read($id): JsonResponse
    {
        $meeting = MeetingAvailability::where([
            'id' => $id
        ])->with(['user', 'participants'])->first();
        if($meeting){
            return response()->json(['meeting' => $meeting]);
        }else{
            return response()->json(['error' => 'Meeting not found'], 404);
        }
    }
    /**
     * @OA\Get(
     *     tags={"meeting_availability"},
     *     path="/api/seller/availability",
     *     summary="Get all meeting availabilities",
     *
     *     @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="page", type="string", format="page", example="1"),
     *              @OA\Property(property="per_page", type="string", format="per_page", example="10"),
     *          ),
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="response",
     *           @OA\JsonContent(
     *              @OA\Property(property="current_page", type="string", format="current_page", example="1"),
     *              @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/MeetingAvailability")),
     *              @OA\Property(property="first_page_url", type="string", format="first_page_url", example="http://api-public.timepal.local/api/seller/availability?page=1"),
     *              @OA\Property(property="from", type="string", format="from", example="1"),
     *              @OA\Property(property="last_page", type="string", format="last_page", example="2"),
     *              @OA\Property(property="last_page_url", type="string", format="last_page_url", example="http://api-public.timepal.local/api/seller/availability?page=2"),
     *              @OA\Property(property="links", type="array",
     *                  example={
     *                  {
     *                      "url": null,
     *                      "label": "&laquo; Previous",
     *                      "active": false
     *                  },
     *                  {
     *                      "url": "http://api-public.timepal.local/api/seller/availability?page=1",
     *                      "label": "1",
     *                      "active": true
     *                  },
     *                  {
     *                      "url": "http://api-public.timepal.local/api/seller/availability?page=2",
     *                      "label": "2",
     *                      "active": false
     *                  },
     *                  {
     *                      "url": "http://api-public.timepal.local/api/seller/availability?page=2",
     *                      "label": "Next &raquo;",
     *                      "active": false
     *                  }},
     *                  @OA\Items(
     *                      @OA\Property(property="url", type="string", format="url"),
     *                      @OA\Property(property="label", type="string", format="label"),
     *                      @OA\Property(property="active", type="string", format="active")
     *                  ),
     *              ),
     *              @OA\Property(property="next_page_url", type="string", format="next_page_url", example="http://api-public.timepal.local/api/seller/availability?page=2"),
     *              @OA\Property(property="path", type="string", format="path", example="http://api-public.timepal.local/api/seller/availability"),
     *              @OA\Property(property="per_page", type="string", format="per_page", example="10"),
     *              @OA\Property(property="prev_page_url", type="string", format="prev_page_url", example="null"),
     *              @OA\Property(property="to", type="string", format="to", example="10"),
     *              @OA\Property(property="total", type="string", format="total", example="12"),
     *          )
     *      )
     * )
     *
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        $user = auth()->user();
        if(!$user) {
            return response()->json(['error' => 'Current user not found'], 404);
        }
        if(!$user->seller){
            return response()->json(['error' => 'Current user has no seller settings'], 406);
        }

        $per_page = request('per_page') ?: 10;
        $meeting = MeetingAvailability::where([
            'user_id' => $user->id
        ])->with(['participants'])->paginate($per_page);

        if(!$meeting){
            return response()->json(['error' => 'Meeting not found'], 404);
        }

        return response()->json(['meeting' => $meeting]);
    }

    /**
     * @OA\Post(
     *     tags={"meeting_availability"},
     *     path="/api/seller/availability/{id}",
     *     summary="Update info",
     *
     *     @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="string", format="id", example="1"),
     *              @OA\Property(property="name", type="string", format="name", example="name"),
     *              @OA\Property(property="description", type="string", format="description", example="some description"),
     *              @OA\Property(property="starting_at", type="string", format="date", example="2021-01-01 01:01:01"),
     *          ),
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="response",
     *           @OA\JsonContent(
     *               @OA\Property(property="meeting", type="object", ref="#/components/schemas/MeetingAvailability")
     *          )
     *      )
     * )
     *
     *
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = auth()->user();
        if(!$user) {
            return response()->json(['error' => 'Current user not found'], 404);
        }
        if(!$user->seller){
            return response()->json(['error' => 'Current user has no seller settings'], 406);
        }

        $meeting = MeetingAvailability::where([
            'id' => $id,
            'user_id' => $user->id,
        ])->with(['user'])->first();

        if(!$meeting){
            return response()->json(['error' => "Can't find this meet, assigned to current user"], 404);
        }

        $meeting->name = $request->input('name');
        $meeting->description = $request->input('description');
        $meeting->starting_at = $request->input('starting_at');
        $meeting->save();

        $Journal = new Journal;
        $Journal->add_event([
            'primary_id'=> $user->id,
            'secondary_id'=> $meeting->id,
            'type'=> 'meeting_availability_update',
            'description'=> 'Update meeting availability',
            'details'=> $meeting->toJson(),
        ]);

        return response()->json(['message' => 'Successfully updated!']);

    }

    /**
     * @OA\Delete (
     *     tags={"meeting_availability"},
     *     path="/api/seller/availability/{id}",
     *     summary="Decline",
     *
     *     @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="string", format="id", example="1")
     *          ),
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="response",
     *           @OA\JsonContent(
     *               @OA\Property(property="message", type="string", example="Successfully declined!")
     *          )
     *      )
     * )
     *
     * @param $id
     * @return JsonResponse
     */
    public function decline($id): JsonResponse
    {
        $user = auth()->user();
        if(!$user) {
            return response()->json(['message' => 'Current user not found']);
        }
        if(!$user->seller){
            return response()->json(['error' => 'Current user has no seller settings'], 406);
        }

        $meeting = MeetingAvailability::where('id', $id)->first();
        if(!$meeting){
            return response()->json(['error' => 'Unknown meeting.'], 404);
        }
        if($meeting->user_id !== $user->id){
            return response()->json(['error' => 'This meeting belongs to another user.'], 403);
        }

        $meeting->declined_at = Carbon::now();
        $meeting->save();

        $Journal = new Journal;
        $Journal->add_event([
            'primary_id'=> $user->id,
            'secondary_id'=> $meeting->id,
            'type'=> 'meeting_availability_decline',
            'description'=> 'Decline meeting availability',
            'details'=> $meeting->toJson(),
        ]);

        return response()->json(['message' => 'Successfully declined!']);

    }
    /**
     * @OA\Get (
     *     tags={"meeting_availability"},
     *     path="/api/seller/{user_id}/availability",
     *     summary="Get seller availability",
     *
     *     @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="user_id", type="string", format="user_id", example="1")
     *          ),
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="response",
     *           @OA\JsonContent(
     *               @OA\Property(property="meeting_availabilities", type="array", @OA\Items(
     *                  @OA\Property(property="id", type="string", format="id", example="1"),
     *                  @OA\Property(property="user_id", type="string", format="user_id", example="1"),
     *                  @OA\Property(property="name", type="string", format="name", example="name"),
     *                  @OA\Property(property="description", type="string", format="description", example="description"),
     *                  @OA\Property(property="starting_at", type="string", format="starting_at", example="2021-01-01 01:01:01"),
     *                  @OA\Property(property="duration", type="string", format="duration", example="31"),
     *                  @OA\Property(property="finished_at", type="string", format="finished_at", example="2021-03-18T09:49:02.000000Z"),
     *                  @OA\Property(property="declined_at", type="string", format="declined_at", example="2021-03-18T09:49:02.000000Z"),
     *                  @OA\Property(property="created_at", type="string", format="created_at", example="2021-03-18T09:49:02.000000Z"),
     *                  @OA\Property(property="updated_at", type="string", format="updated_at", example="2021-03-18T09:49:02.000000Z"),
     *              ))
     *          )
     *      )
     * )
     *
     * @param $user_id
     * @return JsonResponse
     */
    public function get_seller_availability($user_id): JsonResponse
    {
        $user = User::find($user_id);
        if(!$user) {
            return response()->json(['message' => 'Current user not found']);
        }
        if(!$user->seller){
            return response()->json(['error' => 'Current user has no seller settings'], 406);
        }
        if(!$user->MeetingAvailability){
            return response()->json(['error' => 'Meeting Availability not found'], 406);
        }

        return response()->json(['meeting_availabilities' => $user->MeetingAvailability]);

    }

}
