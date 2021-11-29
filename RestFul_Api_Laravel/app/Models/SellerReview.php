<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     title="SellerReview",
 *     @OA\Property(property="id", type="string", format="id", example="1"),
 *     @OA\Property(property="user_id", type="string", format="user_id", example="1"),
 *     @OA\Property(property="seller_id", type="string", format="seller_id", example="1"),
 *     @OA\Property(property="review", type="string", format="review", example="review"),
 *     @OA\Property(property="created_at", type="string", format="created_at", example="2021-03-18T09:49:02.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="updated_at", example="2021-03-18T09:49:02.000000Z"),
 * )
 */
class SellerReview extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'seller_id',
        'review',
    ];

    public function seller_user()
    {
        return $this->belongsTo(User::class, 'users', 'seller_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
