<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *    title="BuyingQueueItem",
 *    @OA\Property(property="id", type="int", format="id", example=""),
 *    @OA\Property(property="buyer_account_id", type="int", format="buyer_account_id", example=""),
 *    @OA\Property(property="seller_account_id", type="int", format="seller_account_id", example=""),
 *    @OA\Property(property="meeting_hash", type="string", format="meeting_hash", example=""),
 *    @OA\Property(property="type", type="string", format="type", example=""),
 *    @OA\Property(property="session_id", type="string|null", format="session_id", example=""),
 *    @OA\Property(property="expired_date", type="\Carbon\Carbon|null", format="expired_date", example=""),
 *    @OA\Property(property="data", type="string|null", format="data", example=""),
 *    @OA\Property(property="error_data", type="string|null", format="error_data", example=""),
 *    @OA\Property(property="amount", type="int", format="amount", example=""),
 *    @OA\Property(property="status", type="string", format="status", example=""),
 *    @OA\Property(property="created_at", type="\Carbon\Carbon|null", format="created_at", example=""),
 *    @OA\Property(property="updated_at", type="\Carbon\Carbon|null", format="updated_at", example=""),
 *    
 * )
 */
class BuyingQueueItem extends Model
{
	protected $table = 'buying_queue_item';

	protected $casts = [
		'buyer_account_id' => 'int',
		'seller_account_id' => 'int',
		'amount' => 'int'
	];

	protected $dates = [
		'expired_date'
	];

	protected $fillable = [
		'buyer_account_id',
		'seller_account_id',
		'meeting_hash',
		'type',
		'expired_date',
		'data',
		'error_data',
		'amount',
		'status'
	];
}
