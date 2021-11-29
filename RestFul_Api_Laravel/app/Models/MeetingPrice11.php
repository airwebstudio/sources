<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *    title="MeetingPrice11",
 *    @OA\Property(property="id", type="int", format="id", example=""),
 *    @OA\Property(property="meeting_hash", type="string", format="meeting_hash", example=""),
 *    @OA\Property(property="seller_account_id", type="int", format="seller_account_id", example=""),
 *    @OA\Property(property="price", type="int|null", format="price", example=""),
 *    @OA\Property(property="price_per_hour", type="int|null", format="price_per_hour", example=""),
 *    @OA\Property(property="free_time", type="int|null", format="free_time", example=""),
 *    @OA\Property(property="created_at", type="\Carbon\Carbon|null", format="created_at", example=""),
 *    @OA\Property(property="updated_at", type="\Carbon\Carbon|null", format="updated_at", example=""),
 *    
 * )
 */
class MeetingPrice11 extends Model
{
	protected $table = 'meeting_price_1_1_fixed';

	protected $casts = [
		'seller_account_id' => 'int',
		'price' => 'int',
		'price_per_hour' => 'int',
		'free_time' => 'int'
	];

	protected $fillable = [
		'meeting_hash',
		'seller_account_id',
		'price',
		'price_per_hour',
		'free_time'
	];
}
