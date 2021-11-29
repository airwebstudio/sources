<?php

namespace App\Http\Controllers;

use App\Models\MeetingProposal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Meeting;
use Illuminate\Support\Str;

class ProposalController extends Controller
{

    /**
     * Create a new MeetController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');

        $this->middleware(function ($request, $next) {
            return $next($request);
        });
    }


    /**
     * @OA\Put(
     *     tags={"proposal"},
     *     path="/api/proposal",
     *     summary="Propos to add new Meet",
     *
     *     @OA\RequestBody(
     *          required=true,
     *          description="type: 1:1 or 1:2 or 2:2",
     *          @OA\JsonContent(
     *              @OA\Property(property="user_id", type="string", format="string", example="1"),
     *              @OA\Property(property="name", type="string", format="string", example="some name"),
     *              @OA\Property(property="description", type="string", format="string", example="some description"),
     *              @OA\Property(property="starting_at", type="string", format="date", example="2021-01-01 01:01:01"),
     *              @OA\Property(property="type", type="string", format="type", example="1:2", description="1:1 or 1:2 or 2:2"),
     *          ),
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="response",
     *           @OA\JsonContent(
     *               @OA\Property(property="message", type="string", example="Successfully added!"),
     *               @OA\Property(property="meeting_proposal", type="object", ref="#/components/schemas/MeetingProposal")
     *          )
     *      )
     * )
     *
     *  Propose to add new Meet
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

        if(!in_array($request->input('type'), MeetingProposal::MEETING_TYPE, true)){
            return response()->json(['error' => 'Meeting type not defined']);
        }

        $user_id = $request->input('user_id');
        $user_to_add = User::find($user_id);
        if(!$user_to_add){
            return response()->json(['error' => 'User not found'], 404);
        }
        if(!$user_to_add->seller){
            return response()->json(['error' => 'Current user has no seller settings'], 406);
        }

        $MeetProposal = MeetingProposal::create([
            'user_id' => $user_to_add->id,
            'proposal_user_id' => $user->id,
            'name' => $request->input('name'),
            'description' => $request->input('description') ,
            'type' => $request->input('type'),
            'starting_at' => $request->input('starting_at')
        ]);

        return response()->json([
            'message' => 'Successfully created!',
            'meeting_proposal' => $MeetProposal
        ]);

    }

    /**
     * @OA\Post(
     *     tags={"proposal"},
     *     path="/api/proposal/{id}",
     *     summary="Approve Propos to add new Meet",
     *
     *     @OA\RequestBody(
     *          required=true,
     *          description="id",
     *          @OA\JsonContent(
     *               @OA\Property(property="id", type="string", format="id", example="1")
     *          ),
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="response",
     *           @OA\JsonContent(
     *               @OA\Property(property="message", type="string", example="Successfully approved!"),
     *               @OA\Property(property="meeting", type="object", ref="#/components/schemas/Meeting")
     *          )
     *      )
     * )
     *
     * Approve Propose to add new Meet
     *
     * @param $id
     * @return JsonResponse
     */
    public function approve($id): JsonResponse
    {

        $user = auth()->user();

        $MeetingProposal = MeetingProposal::find($id);
        if(!$MeetingProposal){
            return response()->json(['error' => 'Meet Proposals not found'], 404);
        }
        if($user->id != $MeetingProposal->user_id){
            return response()->json(['error' => 'You don\'t have permission'], 404);
        }

        $meeting = Meeting::create([
            'hash' => (string) Str::uuid(),
            'user_id' => $user->id,
            'name' => $MeetingProposal->name,
            'description' => $MeetingProposal->description,
            'starting_at' => $MeetingProposal->starting_at,
        ]);

        if(!$meeting){
            return response()->json(['error' => 'Not created'], 403);
        }

        $meeting->addParticipant($MeetingProposal->user_id);

        if($MeetingProposal->propose_participants){
            foreach ($MeetingProposal->propose_participants as $participant){
                $meeting->addParticipant($participant->user_id);
                $participant->delete();
            }
        }

        $MeetingProposal->delete();

        $meeting->user->seller->calculate_meetings_participants_count();

        return response()->json([
            'message' => 'Successfully approved!',
            'meeting' => $meeting
        ]);

    }

    /**
     * @OA\Delete(
     *     tags={"proposal"},
     *     path="/api/proposal/{id}",
     *     summary="Decline Meeting proposal",
     *
     *     @OA\RequestBody(
     *          required=true,
     *          description="id",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="string", format="id", example="id")
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
     * Delete meeting
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

        $meeting = MeetingProposal::where('id', $id)->first();
        if(!$meeting){
            return response()->json(['error' => 'Unknown meeting.'], 404);
        }
        if($meeting->user_id !== $user->id){
            return response()->json(['error' => 'This meeting belongs to another user.'], 403);
        }

        $meeting->declined_at = Carbon::now();
        $meeting->save();

        return response()->json(['message' => 'Successfully declined!']);
    }

    /**
     * @OA\Put (
     *     tags={"proposal"},
     *     path="/api/proposal/{id}/{user_id}",
     *     summary="Propose user to Proposed Meeting",
     *
     *     @OA\RequestBody(
     *          required=true,
     *          description="id, user_id",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="string", format="id", example="id"),
     *              @OA\Property(property="user_id", type="string", format="user_id", example="user_id")
     *          ),
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="response",
     *           @OA\JsonContent(
     *               @OA\Property(property="message", type="string", example="Successfully added!")
     *          )
     *      )
     * )
     *
     * Add user Propose Meet
     *
     * @param $id
     * @param $user_id
     * @return JsonResponse
     */
    public function add_user_to_propose_meeting($id, $user_id): JsonResponse
    {
        $MeetingProposal = MeetingProposal::find($id);
        if(!$MeetingProposal){
            return response()->json(['error' => 'Meet Proposals not found'], 404);
        }

        $user_to_add = User::find($user_id);
        if(!$user_to_add){
            return response()->json(['error' => 'User not exist.'], 403);
        }

        $participants_count = $MeetingProposal->propose_participants->where('user_id', $user_id)->count();
        if($participants_count){
            return response()->json(['error' => 'User already proposed.'], 403);
        }

        $MeetingProposal->proposeParticipant($user_id);
        return response()->json(['message' => 'Successfully added!']);

    }

    /**
     * @OA\Delete (
     *     tags={"proposal"},
     *     path="/api/proposal/{id}/{user_id}",
     *     summary="Remove Proposed user from Proposed Meeting",
     *
     *     @OA\RequestBody(
     *          required=true,
     *          description="id, user_id",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="string", format="id", example="id"),
     *              @OA\Property(property="user_id", type="string", format="user_id", example="user_id")
     *          ),
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="response",
     *           @OA\JsonContent(
     *               @OA\Property(property="message", type="string", example="Successfully deleted!")
     *          )
     *      )
     * )
     *
     * Remove user Propose Meet
     *
     * @param $id
     * @param $user_id
     * @return JsonResponse
     */
    public function remove_user_from_propose_meeting($id, $user_id){

        $MeetingProposal = MeetingProposal::find($id);
        if(!$MeetingProposal){
            return response()->json(['error' => 'Meet Proposals not found'], 404);
        }

        $current_user = auth()->user();

        $user_to_add = User::find($user_id);
        if(!$user_to_add){
            return response()->json(['error' => 'User not exist.'], 403);
        }

        if($current_user->id != $user_id && $MeetingProposal->user_id != $current_user->id) {
            return response()->json(['error' => 'You don\'t have access.'], 403);
        }

        $participants_count = $MeetingProposal->propose_participants->where('user_id', $user_id);
        if(!$participants_count->count()){
            return response()->json(['error' => 'User not proposed to this meet.'], 403);
        }else{
            $participants_count->first()->delete();
            return response()->json(['message' => 'Successfully deleted!']);
        }

    }


    /**
     * @OA\Post (
     *     tags={"proposal"},
     *     path="/api/proposal/my/own",
     *     summary="Get My own",
     *
     *     @OA\Response(
     *          response=200,
     *          description="response",
     *           @OA\JsonContent(
     *               @OA\Property(property="meetings", type="array", @OA\Items(ref="#/components/schemas/MeetingProposal"))
     *          )
     *      )
     * )
     *
     * @return JsonResponse
     */
    public function my_own(): JsonResponse
    {
        $user = auth()->user();
        if(!$user) {
            return response()->json(['error' => 'Current user not found'], 404);
        }

        $meetings = MeetingProposal::where([
            'user_id' => $user->id
        ])->with(['user', 'proposalUser'])->get();

        return response()->json(['meetings' => $meetings]);
    }

    /**
     * @OA\Post (
     *     tags={"proposal"},
     *     path="/api/proposal/my/requests",
     *     summary="Get My Requests",
     *
     *     @OA\Response(
     *          response=200,
     *          description="response",
     *           @OA\JsonContent(
     *               @OA\Property(property="meetings", type="array", @OA\Items(ref="#/components/schemas/Meeting"))
     *          )
     *      )
     * )
     *
     * @return JsonResponse
     */
    public function my_requests(): JsonResponse
    {
        $user = auth()->user();
        if(!$user) {
            return response()->json(['error' => 'Current user not found'], 404);
        }

        $meetings = MeetingProposal::where([
            'proposal_user_id' => $user->id
        ])->with(['user', 'proposalUser'])->get();

        return response()->json(['meetings' => $meetings]);
    }

}
