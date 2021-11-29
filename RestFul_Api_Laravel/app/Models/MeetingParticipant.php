<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     title="MeetingParticipant",
 *     @OA\Property(property="id", type="string", format="id", example="1"),
 *     @OA\Property(property="meeting_id", type="string", format="meeting_id", example="1"),
 *     @OA\Property(property="user_id", type="string", format="user_id", example="1"),
 *     @OA\Property(property="created_at", type="string", format="created_at", example="2021-03-18T09:49:02.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="updated_at", example="2021-03-18T09:49:02.000000Z"),
 * )
 */
class MeetingParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'meeting_hash',
        'user_id',
        'avatar_animal_name',
        'avatar_animal_color',
        'min_price_approved',
        'min_price_paid',
        
    ];

    protected $casts = [
        'min_price_approved' => 'int',
        'min_price_paid' => 'float',
    ];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


}
