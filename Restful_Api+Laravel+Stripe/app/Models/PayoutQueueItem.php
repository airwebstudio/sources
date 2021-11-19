<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *    title="PayoutQueueItem",
 *    @OA\Property(property="id", type="int", format="id", example=""),
 *    @OA\Property(property="session_id", type="string|null", format="session_id", example=""),
 *    @OA\Property(property="internal_user_id", type="int", format="internal_user_id", example=""),
 *    @OA\Property(property="amount", type="int", format="amount", example=""),
 *    @OA\Property(property="data", type="string|null", format="data", example=""),
 *    @OA\Property(property="error_data", type="string|null", format="error_data", example=""),
 *    @OA\Property(property="status", type="string", format="status", example=""),
 *    @OA\Property(property="created_at", type="\Carbon\Carbon|null", format="created_at", example=""),
 *    @OA\Property(property="updated_at", type="\Carbon\Carbon|null", format="updated_at", example=""),
 *    
 * )
 */
class PayoutQueueItem extends Model
{
	protected $table = 'payout_queue_item';

	protected $casts = [
		'internal_user_id' => 'int',
		'amount' => 'int'
	];

	protected $fillable = [
		'internal_user_id',
		'amount',
		'data',
		'error_data',
		'status'
	];
}
