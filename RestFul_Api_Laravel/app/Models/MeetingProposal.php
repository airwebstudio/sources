<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     title="MeetingProposal",
 *     @OA\Property(property="id", type="string", format="id", example="1"),
 *     @OA\Property(property="user_id", type="string", format="user_id", example="1"),
 *     @OA\Property(property="name", type="string", format="name", example="name"),
 *     @OA\Property(property="description", type="string", format="description", example="description"),
 *     @OA\Property(property="type", type="string", format="type", example="1:m"),
 *     @OA\Property(property="starting_at", type="string", format="starting_at", example="2021-03-18T09:49:02.000000Z"),
 *     @OA\Property(property="finished_at", type="string", format="finished_at", example="2021-03-18T09:49:02.000000Z"),
 *     @OA\Property(property="duration", type="string", format="duration", example="31"),
 *     @OA\Property(property="created_at", type="string", format="created_at", example="2021-03-18T09:49:02.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="updated_at", example="2021-03-18T09:49:02.000000Z"),
 *     @OA\Property(property="declined_at", type="string", format="declined_at", example="2021-03-18T09:49:02.000000Z"),
 *     @OA\Property(property="proposal_user_id", type="string", format="proposal_user_id", example="12"),
 * )
 */class MeetingProposal extends Model
{
    use HasFactory;

    const MEETING_TYPE = [
        '1:1',
        '1:m',
        'm:m',
    ];

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'type',
        'duration',
        'declined_at',
        'starting_at',
        'finished_at',
        'proposal_user_id',
    ];

    public function proposalUser(){
        return $this->belongsTo(User::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function proposeParticipant($user_id){
        return MeetingProposalParticipant::create([
            'meeting_proposal_id' => $this->id,
            'user_id' => $user_id
        ]);
    }

    public function propose_participants()
    {
        return $this->hasMany(MeetingProposalParticipant::class);
    }

}
