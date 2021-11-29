<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     title="MeetingAvailabilityParticipant",
 *     @OA\Property(property="id", type="string", format="id", example="1"),
 *     @OA\Property(property="meeting_availability_id", type="string", format="meeting_availability_id", example="1"),
 *     @OA\Property(property="meeting_id", type="string", format="meeting_id", example="1"),
 *     @OA\Property(property="user_id", type="string", format="user_id", example="1"),
 *     @OA\Property(property="created_at", type="string", format="created_at", example="2021-03-18T09:49:02.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="updated_at", example="2021-03-18T09:49:02.000000Z"),
 * )
 */
class MeetingAvailabilityParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_availability_id',
        'meeting_id',
        'user_id',
    ];

    public function meeting_availability()
    {
        return $this->belongsTo(MeetingAvailability::class);
    }

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
