<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *    title="MeetingDynamicalPrice",
 *    @OA\Property(property="id", type="int", format="id", example=""),
 *    @OA\Property(property="meeting_hash", type="string", format="meeting_hash", example=""),
 *    @OA\Property(property="from_participants_count", type="int", format="from_participants_count", example=""),
 *    @OA\Property(property="price", type="float", format="price", example=""),
 *    @OA\Property(property="created_at", type="\Carbon\Carbon|null", format="created_at", example=""),
 *    @OA\Property(property="updated_at", type="\Carbon\Carbon|null", format="updated_at", example=""),
 *    
 * )
 */
class MeetingDynamicalPrice extends Model
{
	protected $table = 'meeting_dynamical_prices';

	protected $casts = [
		'from_participants_count' => 'int',
		'price' => 'float',
	];

	protected $fillable = [		
		'count',
		'price',
		'meeting_hash',
	];
}
