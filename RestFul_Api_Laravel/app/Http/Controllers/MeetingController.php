<?php

namespace App\Http\Controllers;

use App\Journal\Journal;
use App\Models\MeetingProposal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Meeting;
use App\Models as Models;
use Illuminate\Support\Str;
use App\Jobs\DataSynchronization;

class MeetingController extends Controller
{

    /**
     * Create a new MeetingController instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth:api', ['except' => ['calendar', 'upcoming_events']]);

        // $this->middleware(function ($request, $next) {
        //      return $next($request);
        // });
    }

    /**
     * @OA\Put(
     *     tags={"meeting"},
     *     path="/api/meeting",
     *     summary="Create meeting",
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
     *               @OA\Property(property="meeting", type="object", ref="#/components/schemas/Meeting")
     *          )
     *      )
     * )
     *
     * Create new meeting
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

        if(!in_array($request->input('type'), Meeting::MEETING_TYPE, true)){
            return response()->json(['error' => 'Meeting type not defined']);
        }

        $hash = (string)Str::uuid();

        $meeting = Meeting::create([
            'hash' => $hash,
            'user_id' => $user->id,
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'type' => $request->input('type'),
            'starting_at' => $request->input('starting_at'),
            'payment_type' => $request->input('payment_type'),
            'block_minutes' => $request->input('block_minutes', '1'),
        ]);

        $price_row = $meeting->get_price_row();
        $price = $request->input('price', 0);


        if ($meeting->payment_type == 'per_min') {
            if ($min_price = $request->input('min_price', false)) {
                $price_row->min_price = $min_price;
            }
        }

        //return response()->json([$hash]);

        if (!is_numeric($price)) {
            if (($request->input('type') == '1:m') && ($price_arr = explode(';', $price))) {
                foreach ($price_arr as $pr) {
                    if (($pr_items = explode(':', $pr)) && (sizeof($pr_items) == 2)) {
                        Models\MeetingDynamicalPrice::create([
                            'meeting_hash' => $hash,
                            'count' => $pr_items[0],
                            'price' => $pr_items[1],
                        ]);
                    }
                }

                $price_row->is_dynamic_price = true;

                //TODO: check is correct prices schema
            }

            else {
                throw new \Exception('Wrong data');
            }
        }
        else {
            $price_row->price = $price;
        }

        $price_row->save();


        $user->seller->calculate_meetings_count();

        $Journal = new Journal;
        $Journal->add_event([
            'primary_id'=> $user->id,
            'secondary_id'=> $meeting->id,
            'type'=> 'meeting_create',
            'description'=> 'Create Meeting',
            'details'=> $meeting->toJson(),
        ]);

        return response()->json([
            'message' => 'Successfully created!',
            'meeting' => $meeting
        ]);

    }

    /**
     * @OA\Get(
     *     tags={"meeting"},
     *     path="/api/meeting/{hash}",
     *     summary="Get meeting info",
     *
     *     @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="hash", type="string", format="hash", example="hash"),
     *          ),
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="response",
     *           @OA\JsonContent(
     *               @OA\Property(property="meeting", type="object", ref="#/components/schemas/Meeting")
     *          )
     *      )
     * )
     *
     *
     * @param $hash
     * @return JsonResponse
     */
    public function info($hash)
    {

        if (!$hash) {
            throw new \Exception('No hash!');
        }

        $meeting = Meeting::where([
            'hash' => $hash
        ])->with(['user', 'participants', 'participants.user','propose_participants'])->first();


        if (!$meeting) {
            throw new \Exception('No meeting with such a hash!');
        }

        $user = auth()->user();

        $meeting->payments = (object)$this->user_request('payments_list', ['meeting_hash' => $hash], true);


        if (is_object($meeting->payments)) {
            if (isset($meeting->payments->seller_sum) && $meeting->payments->seller_sum !== 0) {
                $meeting->seller_sum = '$'.$meeting->payments->seller_sum;
            }


            if (isset($meeting->payments->buyer_sum) && $meeting->payments->buyer_sum !== 0) {
                $meeting->buyer_sum = '$'.$meeting->payments->buyer_sum;
            }

            if (isset($meeting->payments->buyer_reservs_sum) && $meeting->payments->buyer_reservs_sum > 0) {
                $meeting->buyer_reservs_sum = '$'.$meeting->payments->buyer_reservs_sum;
            }
        }

        $meeting['price_info'] = $meeting->get_price_row();

        if (($meeting->user->id == $user->id) && isset($meeting->payments->buyers_sum)) {
            $parr = [];

            foreach ($meeting->participants as $p) {
                $parr[$p->user_id] = $p;
            }



            foreach ($meeting->payments->buyers_sum as $bs) {

                if (isset($parr[$bs['buyer_account_id']])) {
                    $obj = $parr[$bs['buyer_account_id']];
                    $obj->payment_sum = $bs['sum'];
                    $obj->user->payment_sum = $bs['sum'];
                    $parr[$bs['buyer_account_id']] = $obj;
                }


            }


            $meeting->participants = array_values($parr);
        }
        return response()->json(['meeting' => $meeting]);

    }

    /**
     * @OA\Post(
     *     tags={"meeting"},
     *     path="/api/meeting/{hash}",
     *     summary="Update info",
     *
     *     @OA\RequestBody(
     *          required=true,
     *          description="type: 1:1 or 1:m or m:m,  billing_start_type: FIXED_TIME or ON_BUYER_CONNECTED",
     *          @OA\JsonContent(
     *              @OA\Property(property="hash", type="string", format="hash", example="hash"),
     *              @OA\Property(property="name", type="string", format="name", example="name"),
     *              @OA\Property(property="description", type="string", format="description", example="some description"),
     *              @OA\Property(property="starting_at", type="string", format="date", example="2021-01-01 01:01:01"),
     *              @OA\Property(property="type", type="string", format="type", example="1:2", description="1:1 or 1:m or m:m"),
     *              @OA\Property(property="billing_start_type", type="string", format="billing_start_type", example="FIXED_TIME", description="FIXED_TIME or ON_BUYER_CONNECTED"),
     *          ),
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="response",
     *           @OA\JsonContent(
     *               @OA\Property(property="meeting", type="object", ref="#/components/schemas/Meeting")
     *          )
     *      )
     * )
     *
     *
     * @param Request $request
     * @param $hash
     * @return JsonResponse
     */
    public function update(Request $request, $hash): JsonResponse
    {
        $user = auth()->user();
        if(!$user) {
            return response()->json(['error' => 'Current user not found'], 404);

        }
        if(!$user->seller){
            return response()->json(['error' => 'Current user has no seller settings'], 406);
        }

        $meeting = Meeting::where([
            'hash' => $hash,
            'user_id' => $user->id,
        ])->with(['user'])->first();

        if(!$meeting){
            return response()->json(['error' => "Can't find this meet, assigned to current user"], 404);
        }

        if($request->input('name')) {
            $meeting->name = $request->input('name');
        }
        if($request->input('description')) {
            $meeting->description = $request->input('description');
        }
        if($request->input('type')) {
            if (!in_array($request->input('type'), Meeting::MEETING_TYPE, true)) {
                return response()->json(['error' => 'Meeting type not defined']);
            }
            $meeting->type = $request->input('type');
        }
        if($request->input('billing_start_type')) {
            if (!in_array($request->input('billing_start_type'), Meeting::BILLING_START_TYPE, true)) {
                return response()->json(['error' => 'Meeting billing_start_type not defined']);
            }
            $meeting->type = $request->input('billing_start_type');
        }
        if($request->input('auto_approve_after_payment')) {
            $meeting->auto_approve_after_payment = $request->input('auto_approve_after_payment');
        }
        if($request->input('starting_at')) {
            $meeting->starting_at = $request->input('starting_at');
        }
        $meeting->save();

        $Journal = new Journal;
        $Journal->add_event([
            'primary_id'=> $user->id,
            'secondary_id'=> $meeting->id,
            'type'=> 'meeting_update',
            'description'=> 'Update Meeting',
            'details'=> $meeting->toJson(),
        ]);

        return response()->json(['message' => 'Successfully updated!']);
    }

    /**
     * @OA\Delete (
     *     tags={"meeting"},
     *     path="/api/meeting/{hash}",
     *     summary="Decline",
     *
     *     @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="hash", type="string", format="hash", example="hash")
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
     * @param $hash
     * @return JsonResponse
     */
    public function decline($hash): JsonResponse
    {
        $user = auth()->user();
        if($user) {
            if(!$user->seller){
                return response()->json(['error' => 'Current user has no seller settings'], 406);
            }

            $meeting = Meeting::where('hash', $hash)->first();
            if(!$meeting){
                return response()->json(['error' => 'Unknown meeting.'], 404);
            }
            if($meeting->user_id !== $user->id){
                return response()->json(['error' => 'This meeting belongs to another user.'], 403);
            }

            $meeting->declined_at = Carbon::now();
            $meeting->save();

            $user->seller->calculate_meetings_count();

            $Journal = new Journal;
            $Journal->add_event([
                'primary_id'=> $user->id,
                'secondary_id'=> $meeting->id,
                'type'=> 'meeting_decline',
                'description'=> 'Decline Meeting',
                'details'=> $meeting->toJson(),
            ]);

            return response()->json(['message' => 'Successfully declined!']);
        }
        return response()->json(['message' => 'Current user not found']);
    }

    /**
     * @OA\Post (
     *     tags={"meeting"},
     *     path="/api/meeting/my/own",
     *     summary="Get My own",
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
    public function my_own(Request $request): JsonResponse
    {
        $user = auth()->user();
        if(!$user) {
            return response()->json(['error' => 'Current user not found'], 404);
        }

        $meetings = $this->approve_filters(Meeting::where([
            'user_id' => $user->id
        ])->with(['user', 'participants',  'propose_participants']), $request->input('filters', false));


        return response()->json($meetings->paginate(10));

    }

    private function approve_filters($data, $filters) {

        if ($filters)
        foreach ($filters as $filter) {
            if ($filter) {
                $filter = (object) $filter;
                if (($filter->from_to)) {

                    $filter->value = (object) $filter->value;

                    if (isset($filter->value->from) && isset($filter->value->to)) {

                        $data = $data->whereBetween($filter->id, [$filter->value->from, $filter->value->to]);
                    }
                    elseif (isset($filter->value->from)) {
                        $data = $data->where($filter->id, '>=',  $filter->value->from);
                    }
                    elseif (isset($filter->value->to)) {
                        $data = $data->where($filter->id, '<=',  $filter->value->to);
                    }
                }
                elseif ($filter->type == 'select'){
                    $data = $data->where($filter->id, $filter->value);
                }
                else {
                    $data = $data->where($filter->id, 'LIKE',  '%'.$filter->value.'%');
                }

            }
        }

        return $data;
    }

    public function participantof() {

        $user = auth()->user();
        if(!$user) {
            return response()->json(['error' => 'Current user not found'], 404);
        }

        $participantof = $this->approve_filters(Meeting::with(['participants', 'user'])->whereHas('participants', function ($query) use ($user) {
            return $query->where('user_id', '=', $user->id);
        }), request()->input('filters', false));


        return response()->json($participantof->paginate(10));
    }

    /**
     * @OA\Post (
     *     tags={"meeting"},
     *     path="/api/meeting/my/requests",
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

        $meetings = Meeting::whereRaw('
            exists (select id from meeting_participants where meeting_participants.user_id = '.$user->id.')
        ')->with(['user', 'participants', 'propose_participants'])->get();

        return response()->json(['meetings' => $meetings]);

    }

    /**
     * @OA\Post (
     *     tags={"meeting"},
     *     path="/api/meeting/calendar/{user_id}",
     *     summary="Get Calendar by user ID",
     *
     *     @OA\Response(
     *          response=200,
     *          description="response",
     *           @OA\JsonContent(
     *               @OA\Property(property="meetings", type="array", @OA\Items(
     *                  @OA\Property(property="id", type="string", format="id", example="1"),
     *                  @OA\Property(property="hash", type="string", format="hash", example="hash"),
     *                  @OA\Property(property="user_id", type="string", format="user_id", example="1"),
     *                  @OA\Property(property="name", type="string", format="name", example="name"),
     *                  @OA\Property(property="description", type="string", format="description", example="description"),
     *                  @OA\Property(property="starting_at", type="string", format="starting_at", example="2021-01-01 01:01:01"),
     *                  @OA\Property(property="finished_at", type="string", format="finished_at", example="2021-03-18T09:49:02.000000Z"),
     *                  @OA\Property(property="duration", type="string", format="duration", example="31"),
     *                  @OA\Property(property="created_at", type="string", format="created_at", example="2021-03-18T09:49:02.000000Z"),
     *                  @OA\Property(property="updated_at", type="string", format="updated_at", example="2021-03-18T09:49:02.000000Z"),
     *                  @OA\Property(property="declined_at", type="string", format="declined_at", example="2021-03-18T09:49:02.000000Z"),
     *                  @OA\Property(property="user", type="object", ref="#/components/schemas/User"),
     *                  @OA\Property(property="participants", type="array", @OA\Items(ref="#/components/schemas/MeetingParticipant")),
     *              ))
     *          )
     *      )
     * )
     *
     * @param $user_id
     * @return JsonResponse
     */
    public function calendar($user_id): JsonResponse
    {
        $meetings = Meeting::where([
            'user_id' => $user_id
        ])->with(['user'])->get();

        return response()->json(['meetings' => $meetings]);
    }

    /**
     * @OA\Put(
     *     tags={"meeting"},
     *     path="/api/meeting/{hash}/{user_id}",
     *     summary="Add User to Meeting",
     *
     *     @OA\RequestBody(
     *          required=true,
     *          description="hash, user_id",
     *          @OA\JsonContent(
     *              @OA\Property(property="hash", type="string", format="hash", example="hash"),
     *              @OA\Property(property="user_id", type="string", format="user_id", example="user_id")
     *          ),
     *      ),
     *     @OA\Response(
     *          response=200,
     *          description="response",
     *           @OA\JsonContent(
     *               @OA\Property(property="message", type="string", example="Successfully added!")
     *          )
     *      )
     * )
     */
    /**
     * Add User to Meeting
     *
     * @param $hash
     * @param $user_id
     * @return JsonResponse
     */
    public function add_user_to_meeting($hash, $user_id): JsonResponse
    {
        $current_user = auth()->user();
        $user_to_add = User::find($user_id);

        if(!$user_to_add){
            return response()->json(['error' => 'User not exist.'], 403);
        }

        $Meeting = Meeting::where('hash', $hash)->first();
        if(!$Meeting){
            return response()->json(['error' => 'Unknown meeting.'], 404);
        }

        $participants_count = $Meeting->participants->where('user_id', $user_id)->count();
        if($participants_count){
            return response()->json(['error' => 'User already in the meet.'], 403);
        }

        $propose_participants_count = $Meeting->propose_participants->where('user_id', $user_id)->count();
        if($propose_participants_count){
            if($current_user->id == $Meeting->user_id) {
                return $this->approve_add_user_to_meeting($hash, $user_id);
            }
            return response()->json(['error' => 'User already proposed.'], 403);
        }

        if($current_user->id != $Meeting->user_id){
            if($current_user->id == $user_id){
                $Meeting->proposeParticipant($user_id);
                return response()->json(['message' => 'Successfully proposed!']);
            }else{
                return response()->json(['error' => 'You cannot add this user.'], 403);
            }
        }

        $Participant = $Meeting->addParticipant($user_id);

        $Meeting->user->seller->calculate_participants_count();

        $Journal = new Journal;
        $Journal->add_event([
            'primary_id'=> $user_id,
            'secondary_id'=> $Meeting->id,
            'type'=> 'add_user_to_meeting',
            'description'=> 'Add User To Meeting',
            'details'=> $Participant->toJson(),
        ]);

        return response()->json(['message' => 'Successfully added!']);
    }

    /**
     * @OA\Post(
     *     tags={"meeting"},
     *     path="/api/meeting/{hash}/{user_id}",
     *     summary="Approve Add User to Meeting",
     *
     *     @OA\RequestBody(
     *          required=true,
     *          description="hash, user_id",
     *          @OA\JsonContent(
     *              @OA\Property(property="hash", type="string", format="hash", example="hash"),
     *              @OA\Property(property="user_id", type="string", format="user_id", example="user_id")
     *          ),
     *      ),
     *     @OA\Response(
     *          response=200,
     *          description="response",
     *           @OA\JsonContent(
     *               @OA\Property(property="message", type="string", example="Successfully Approved!")
     *          )
     *      )
     * )
     *
     * @param $hash
     * @param $user_id
     * @return JsonResponse
     */
    public function approve_add_user_to_meeting($hash, $user_id): JsonResponse
    {
        $current_user = auth()->user();
        $user_to_add = User::find($user_id);

        if(!$user_to_add){
            return response()->json(['error' => 'User not exist.'], 403);
        }

        $meet = Meeting::where('hash', $hash)->first();
        if(!$meet){
            return response()->json(['error' => 'Unknown meeting.'], 404);
        }

        if($current_user->id !== $meet->user_id){
            return response()->json(['error' => 'You don\'t have access.'], 403);
        }

        $participants = $meet->participants->where('user_id', $user_id);
        if($participants->count()){
            return response()->json(['error' => 'User already in the meet.'], 403);
        }

        $propose_participants = $meet->propose_participants->where('user_id', $user_id);
        if(!$propose_participants->count()){
            return response()->json(['error' => 'User not found in propose to the meet.'], 403);
        }

        $Participant = $meet->addParticipant($user_id);
        $propose_participants->first()->delete();

        $meet->user->seller->calculate_participants_count();

        $Journal = new Journal;
        $Journal->add_event([
            'primary_id'=> $user_id,
            'secondary_id'=> $meet->id,
            'type'=> 'approve_add_user_to_meeting',
            'description'=> 'Approve add user to meeting',
            'details'=> $Participant->toJson(),
        ]);

        return response()->json(['message' => 'Successfully Approved!']);

    }

    /**
     * @OA\Delete(
     *     tags={"meeting"},
     *     path="/api/meeting/{hash}/{user_id}",
     *     summary="Remove User from Meeting",
     *
     *     @OA\RequestBody(
     *          required=true,
     *          description="hash, user_id",
     *          @OA\JsonContent(
     *              @OA\Property(property="hash", type="string", format="hash", example="hash"),
     *              @OA\Property(property="user_id", type="string", format="user_id", example="user_id")
     *          ),
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="response",
     *           @OA\JsonContent(
     *               @OA\Property(property="message", type="string", example="Successfully Removed!")
     *          )
     *      )
     * )
     *
     * @param $hash
     * @param $user_id
     * @return JsonResponse
     */
    public function remove_user_from_meeting($hash, $user_id): JsonResponse
    {
        $current_user = auth()->user();
        $user_to_add = User::find($user_id);

        if(!$user_to_add){
            return response()->json(['error' => 'User not exist.'], 403);
        }

        $Meeting = Meeting::where('hash', $hash)->first();
        if(!$Meeting){
            return response()->json(['error' => 'Unknown meeting.'], 404);
        }

        $participants = $Meeting->participants->where('user_id', $user_id);
        $propose_participants = $Meeting->propose_participants->where('user_id', $user_id);
        if(!$participants->count() && !$propose_participants->count()){
            return response()->json(['error' => 'User not in the meet.'], 403);
        }

        if($current_user->id !== $user_id && $Meeting->user_id !== $current_user->id){
            return response()->json(['error' => 'You cannot manipulate this user.'], 403);
        }

        if($propose_participants->count()){
            $propose_participants->first()->delete();
        }elseif($participants->count()){
            $participants->first()->delete();
        }

        $Meeting->user->seller->calculate_participants_count();

        $Journal = new Journal;
        $Journal->add_event([
            'primary_id'=> $user_id,
            'secondary_id'=> $Meeting->id,
            'type'=> 'remove_user_from_meeting',
            'description'=> 'Remove user from meeting - Successfully deleted!',
            'details'=> $Meeting->toJson(),
        ]);

        return response()->json(['message' => 'Successfully deleted!']);
    }

    /**
     * @OA\Get (
     *     tags={"meeting"},
     *     path="/api/meeting/upcoming_events",
     *     summary="Get Upcoming Events",
     *
     *     @OA\RequestBody(
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="count_days", type="string", format="count_days", example="10"),
     *              @OA\Property(property="limit", type="string", format="limit", example="10")
     *          ),
     *      ),
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
     * @param Request $request
     * @return JsonResponse
     */
    public function upcoming_events(Request $request): JsonResponse
    {
        $limit = $request->input('limit') ?: 10;
        $count_days = $request->input('count_days') ?: 30;

        $from = Carbon::now();
        $to = Carbon::now()->addDays($count_days);

        $meetings = Meeting::whereBetween('starting_at', [$from, $to])->limit($limit)->orderBy('starting_at', 'asc')->get();

        return response()->json(['meetings' => $meetings]);
    }

    private function user_request($api, $params = [], $return_data = false) {
        $sserver = config('app.stripe_server');
        if ($user = auth()->user()) {
            $params['user'] = $user->toArray();
        }


        //$user = Models\User::find(1);
        $params = array_merge($params, request()->all());

        $data = Http::post($sserver.'/api/stripe/'.$api, $params)->json();
        return ($return_data) ? $data : response()->json($data);
    }


    public function join_meeting(Request $request ) {

        if (!$user = auth()->user()) {
            throw new \Exception('No logged user');
        }

        // $user = (object)['id' => 1];

        if (!$meeting_hash = $request->input('meeting_hash', false)) {
            throw new \Exception('No meeting hash');
        }

        if (!$meeting = Models\Meeting::where('hash', $meeting_hash)->first()) {
            throw new \Exception('No meeting with this hash');
        }

        if (($meeting->type == '1:1') && ($meeting->participants()->count() >= 1)) {
            throw new \Exception('Meeting only for 1 participant!');
        }

        $price_info = $meeting->get_price_row();

        if ((($meeting->type == '1:m')
            && ($meeting->payment_type == 'fixed')
            && ($price_info->is_dynamic_price))
            || ((!$price_info->is_dynamic_price)
            && ($price_info->price == 0))) {

            $meeting->addParticipant($user->id);
            return response()->json(['status' => 'Done']);

         }

        $type= 'join_meeting';

        if ($meeting->payment_type == 'fixed') {
            $price = $price_info->price;
        }
        elseif ($meeting->payment_type == 'per_min') {
            $price = $price_info->min_price;
        }


        if ($price == 0) {
            $meeting->addParticipant($user->id);
            return response()->json(['status' => 'Done']);
        }

        return $this->user_request('reserve', ['seller_id' => $price_info->seller_account_id, 'amount' => $price, 'type' => $type]);

    }

    public function finish_meeting() {
        if (!$meeting_hash = request()->input('meeting_hash', false)) {
            throw new \Exception('No meeting hash');
        }

        if (!$user = auth()->user()) {
            throw new \Exception('No logged user');
        }

        if (!$meet = Models\Meeting::where([['hash', $meeting_hash], ['user_id', $user->id]])) {
            throw new \Exception('You haven\'t create this meeting!');
        }


        $meet->update([
            'finished_at' => \DB::raw('now()'),

            'status' => 'finished'
        ]);

        //Models\MeetingParticipant::where('meeting_hash', $meeting_hash)->delete();

        return $this->user_request('approve_reserve');
    }

    public function start_meeting() {

       

        if (!$meeting_hash = request()->input('meeting_hash', false)) {
            throw new \Exception('No meeting hash');
        }

        if (!$meeting = Models\Meeting::where('hash', $meeting_hash)->first()) {
            throw new \Exception('No meeting with this hash');
        }
        

        $prow = $meeting->get_price_row();

        if (($meeting->type == '1:m') && ($meeting->payment_type == 'fixed') && ($prow->is_dynamic_price)) {

            $price = $meeting->get_price();
            return $this->user_request('reserve', ['seller_id' => $prow->seller_account_id, 'amount' => $price, 'users' => json_encode($meeting->participants, 1)]);
        }

        $meeting->started_at = date('Y-m-d H:i:s');
        $meeting->status = 'processing';
        $meeting->save();

        // if ($meeting->payment_type == 'per_min')
        //     return $this->user_request('approve_reserve');
    }


    public function pay_minute(Request $request) {


        if (!$meeting_hash = $request->input('meeting_hash', false)) {
            throw new \Exception('No meeting hash');
        }

        if (!$meeting = Models\Meeting::where('hash', $meeting_hash)->first()) {
            throw new \Exception('No meeting with this hash');
        }

        if ($meeting->payment_type != 'per_min') {
            throw new \Exception('Not minute payment');
        }


        $price = $meeting->get_price(); //getting price for current min

        

        $seller_id = $meeting->get_price_row()->seller_account_id;

        $pp = [];

        $aprvs = [];

        $min_price = $meeting->get_price_row()->min_price;

        $bmin = $meeting->block_minutes;

        foreach ($meeting->participants as $p) {

            if (($p->min_price_approved == true) || ( $min_price == 0)) {
                

                foreach ($meeting->participants as $p) {
                    

                    if ($p->block_minutes_counter == 0) {
                        $p1 = (object)['user_id' => $p->user_id];
                        $p1->amount = $bmin*$price;
                        $p1->seller_id = $seller_id;
                        $p1->meeting_hash = $meeting->hash;
                        $p1->description = 'Block '.$bmin.' minute(s) payment min for meeting #'.$meeting->hash;
                        $pp[] = $p1;

                        $p->save();

                        
                    }

                    if ($p->block_minutes_counter >= $bmin-1) {
                        $p->block_minutes_counter = 0;
                        $p->save();
                    }
                    else {
                        $p->block_minutes_counter += 1;
                        $p->save();
                    }

                }


            }
            else {
                if ($p->min_price_paid + $price > $min_price) {
                    $pp[] = $p;
                    $aprvs[] = $p;

                    if ($d = $min_price - $p->min_price_paid > 0) {
                        $p1 = (object)['user_id' => $p->user_id];
                        $p1->amount = -$d;
                        $p1->seller_id = $seller_id;
                        $p1->meeting_hash = $meeting->hash;
                        $p1->description = 'Minimal price payment for meeting #'.$meeting->hash;
                        $pp[] = $p1;
                    }

                    $p->min_price_approved = true;
                    $p->save();
                }
                else {
                    $p->min_price_paid += $price;
                    $p->save();
                }
            }

        }


        if (sizeof($aprvs)) {
            $this->user_request('approve_reserve', ['seller_id' => $meeting->get_price_row()->seller_account_id, 'users' => json_encode($aprvs, 1)]);
        }

        // if (empty($pp)) {
        //     //$pp[] = ['seller_id' => $seller_id, 'amount' => $price, 'meeting_hash' => $meeting->hash, 'description' => 'Regular minute payment for meeting #'.$meeting->hash];
        // }


        return $this->user_request('payment', ['payments' => $pp]);
    }

    public function data_synchronization($meeting_hash) {
//         if (!Meeting::where('hash', $meeting_hash)->first()) {
//             return response()->json(['error' => 'Meeting not found'], 404);
//         }

        DataSynchronization::dispatch($meeting_hash);
        return response()->json([
            'message' => 'Successfully synchronized.',
        ]);

    }



}
