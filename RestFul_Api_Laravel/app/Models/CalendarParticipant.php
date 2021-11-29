<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * @OA\Schema(
 *     title="CalendarParticipant",
 *     @OA\Property(property="id", type="string", format="id", example="1"),
 *     @OA\Property(property="calendar_id", type="string", format="calendar_id", example="1"),
 *     @OA\Property(property="meeting_id", type="string", format="meeting_id", example="1"),
 *     @OA\Property(property="user_id", type="string", format="user_id", example="1"),
 *     @OA\Property(property="created_at", type="string", format="created_at", example="2021-03-18T09:49:02.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="updated_at", example="2021-03-18T09:49:02.000000Z"),
 * )
 */
class CalendarParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'calendar_id',
        'meeting_id',
        'user_id',
    ];

    public function calendar()
    {
        return $this->belongsTo(Calendar::class);
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
