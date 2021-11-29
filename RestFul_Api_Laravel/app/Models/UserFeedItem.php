<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     title="UserFeedItem",
 *     @OA\Property(property="id", type="string", format="id", example="1"),
 *     @OA\Property(property="user_id", type="string", format="user_id", example="1"),
 *     @OA\Property(property="title", type="string", format="title", example="title"),
 *     @OA\Property(property="description", type="string", format="description", example="description"),
 *     @OA\Property(property="is_active", type="string", format="is_active", example="1"),
 *     @OA\Property(property="created_at", type="string", format="created_at", example="2021-03-18T09:49:02.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="updated_at", example="2021-03-18T09:49:02.000000Z"),
 * )
 */
class UserFeedItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'is_active'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
