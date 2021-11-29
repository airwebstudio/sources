<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;


/**
 * @OA\Schema(
 *     title="Meeting",
 *     @OA\Property(property="id", type="string", format="id", example="1"),
 *     @OA\Property(property="hash", type="string", format="hash", example="hash"),
 *     @OA\Property(property="user_id", type="string", format="user_id", example="1"),
 *     @OA\Property(property="name", type="string", format="name", example="name"),
 *     @OA\Property(property="description", type="string", format="description", example="description"),
 *     @OA\Property(property="type", type="string", format="type", example="1:m"),
 *     @OA\Property(property="billing_start_type", type="string", format="billing_start_type", example="FIXED_TIME"),
 *     @OA\Property(property="auto_approve_after_payment", type="string", format="auto_approve_after_payment", example="0"),
 *     @OA\Property(property="starting_at", type="string", format="starting_at", example="2021-01-01 01:01:01"),
 *     @OA\Property(property="finished_at", type="string", format="finished_at", example="2021-03-18T09:49:02.000000Z"),
 *     @OA\Property(property="duration", type="string", format="duration", example="31"),
 *     @OA\Property(property="created_at", type="string", format="created_at", example="2021-03-18T09:49:02.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="updated_at", example="2021-03-18T09:49:02.000000Z"),
 *     @OA\Property(property="declined_at", type="string", format="declined_at", example="2021-03-18T09:49:02.000000Z"),
 *     @OA\Property(property="user", type="object", ref="#/components/schemas/User"),
 *     @OA\Property(property="participants", type="array", @OA\Items(ref="#/components/schemas/MeetingParticipant")),
 *     @OA\Property(property="propose_participants", type="array", @OA\Items(ref="#/components/schemas/MeetingProposalParticipant")),
 * )
 */
class Meeting extends Model
{
    use HasFactory;

    const MEETING_TYPE = [
        '1:1',
        '1:m',
        'm:m',
    ];

    const BILLING_START_TYPE = [
        'FIXED_TIME',
        'ON_BUYER_CONNECTED',
    ];

    protected $fillable = [
        'hash',
        'name',
        'user_id',
        'description',
        'type',
        'billing_start_type',
        'auto_approve_after_payment',
        'starting_at',
        'finished_at',
        'started_at',
        'duration',
        'payment_type',
        'status',
        'block_minutes',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected $appends = [
        'is_seller', 
        'is_participant', 
        'owner_string', 
        'user_in_meeting', 
        'participants_count',
        'meeting_dates',
        'duration',
    ];

    public function scheduleMeeting($params){

    }

    public function hasParticipant($user_id) {
        return (MeetingParticipant::where([['meeting_hash', $this->hash], ['user_id', $user_id]])->first()) ? true: false;
    }


    public function removeParticipant($user_id) {
        if ($this->hasParticipant($user_id))
            return MeetingParticipant::where([['meeting_hash', $this->hash], ['user_id', $user_id]])->delete();
    }

    public function addParticipant($user_id){
        $animal_name = ["alligator", "anteater", "armadillo", "aurochs", "axolotl", "badger",
                        "bat", "beaver", "buffalo", "camel", "capybara", "chameleon", "cheetah",
                        "chinchilla", "chipmunk", "chupacabra", "cormorant", "coyote", "crow",
                        "dingo", "dinosaur", "dolphin", "duck", "elephant", "ferret", "fox", "frog", "giraffe",
                        "gopher", "grizzly", "hedgehog", "hippo", "hyena", "ibex", "ifrit", "iguana",
                        "jackal", "jackalope", "kangaroo", "koala", "kraken", "leopard", "lemur", "liger",
                        "loris", "manatee", "mink", "monkey", "moose", "narwhal", "Nyan Cat", "orangutan",
                        "otter", "panda", "penguin", "platypus", "pumpkin", "python", "quagga", "rabbit",
                        "raccoon", "rhino", "sheep", "shrew", "skunk", "squirrel", "tiger", "turtle", "walrus", "wolf", "wolverine", "wombat"];
        $animal_color = ["red","orange","yellow","green","blue","purple","teal"];
        //TODO: need add some verification & validation of input data

        if ($this->hasParticipant($user_id) || $this->user_id == $user_id) {
            throw new \Exception('This user already in meeting');
        }

        return MeetingParticipant::create([
            'meeting_id' => $this->id,
            'meeting_hash' => $this->hash,
            'user_id' => $user_id,
            'avatar_animal_name' => $animal_name[array_rand($animal_name, 1)],
            'avatar_animal_color' => $animal_color[array_rand($animal_color, 1)],
        ]);

        //TODO: add action to journal: added perticipant
    }

    public function proposeParticipant($user_id){
        return MeetingProposalParticipant::create([
            'meeting_id' => $this->id,
            'user_id' => $user_id
        ]);
    }


    public function get_price_row() {

        $type = $this->type;
        $ptype = $this->payment_type;

        $meeting_hash = $this->hash;

        if (($ptype == 'fixed') && ($type == '1:1')) {
            if (!$price = MeetingPrice11::where('meeting_hash', $meeting_hash)->first()) {
                $price = new MeetingPrice11();
            }
        }
        elseif (($ptype == 'fixed') && ($type == '1:m')) {
            if (!$price = MeetingPrice1M::where('meeting_hash', $meeting_hash)->first()) {
                $price = new MeetingPrice1M();
            }
        }
        elseif (($ptype == 'per_min') && ($type == '1:1')) {
            if (!$price = MeetingPrice11PerMin::where('meeting_hash', $meeting_hash)->first()) {
                $price = new MeetingPrice11PerMin();
            }
        }
        elseif (($ptype == 'per_min') && ($type == '1:m')) {
            if (!$price = MeetingPrice1MPerMin::where('meeting_hash', $meeting_hash)->first()) {
                $price = new MeetingPrice1MPerMin();

            }
        }

        if (!$price->meeting_hash) {
            $price->seller_account_id = $this->user_id;
            $price->meeting_hash = $this->hash;
        }

        if (($this->type == '1:m') && ($price->is_dynamic_price)) {
           $d = MeetingDynamicalPrice::where('meeting_hash', $this->hash);
           $price->dynamic_price = ['all' => $d->get(), 'min' => $d->min('price'), 'max' => $d->max('price')];

        }

        return $price;


    }

    public function smiles(){
        return $this->hasMany(Smile::class);
    }

    public function messages(){
        return $this->hasMany(Message::class);
    }

    public function system_messages(){
        return $this->hasMany(SystemMessage::class);
    }

    public function questions(){
        return $this->hasMany(Question::class);
    }

    public function meetingReports(){
        return $this->hasMany(MeetingReports::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function participants()
    {
        return $this->hasMany(MeetingParticipant::class);
    }

    public function getParticipantsCountAttribute() {
        return $this->participants()->count();
    }

    public function getMeetingDatesAttribute() {
        return $this->started_at. (($this->finished_at) ? ' - '. $this->finished_at : '');
    }

  


    public function getDurationAttribute() {
        if ($this->started_at && $this->finished_at) {
            return date('H:i:s', strtotime($this->finished_at) - strtotime($this->started_at));
        }
    }


    public function price_string() {

        $price = $this->get_price_row();

        $price_str = ($price->is_dynamic_price) ? 'Dynamic price from $'.$price->dynamic_price['min'].' to $'.$price->dynamic_price['max'] : '$'.$price->price;


        if ($this->paymanet_type == 'per_min') {
            $price_str .= ' per min';
        }

        return $price_str;
    }
    

    public function getIsSellerAttribute() {

       $user = auth()->user();
       if (!$user) return false;
       return (Meeting::where([['user_id', $user->id], ['hash', $this->hash]])->count() > 0);
    }
    
    public function getIsParticipantAttribute() {

       $user = auth()->user();
       if (!$user) return false;
       return (MeetingParticipant::where([['user_id', $user->id], ['meeting_hash', $this->hash]])->count() > 0);
    }


    public function getUserInMeetingAttribute() {
        return ($this->is_seller || $this->is_participant);
    }

    public function getOwnerStringAttribute() {

        $user = auth()->user();
        if (!$user) return false;
        return ($this->user_id == $user->id) ? 'Me' : $this->user()->first()->name.' <'.$this->user()->first()->email.'>';
    }
    

    public function propose_participants()
    {
        return $this->hasMany(MeetingProposalParticipant::class);
    }

    public function attachments()
    {
        return $this->belongsToMany(StorageFile::class, 'meeting_attchments', 'meeting_hash', 'storage_id', 'hash');
    }

    public function get_price() {

        $prow = $this->get_price_row();

        if (($this->type == '1:m') && ($prow->is_dynamic_price)) {
            return MeetingDynamicalPrice::where('meeting_hash', $this->hash)->where('count', '<=', $this->participants()->count())->orderBy('count', 'desc')->first()->price;
        }
        else {
            return $prow->price;
        }

    }
}
