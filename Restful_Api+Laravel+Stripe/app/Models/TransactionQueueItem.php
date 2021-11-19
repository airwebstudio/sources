<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *    title="TransactionQueueItem",
 *    @OA\Property(property="id", type="int", format="id", example=""),
 *    @OA\Property(property="session_id", type="string|null", format="session_id", example=""),
 *    @OA\Property(property="type", type="string", format="type", example=""),
 *    @OA\Property(property="description", type="string|null", format="description", example=""),
 *    @OA\Property(property="amount", type="float", format="amount", example=""),
 *    @OA\Property(property="currency", type="string", format="currency", example=""),
 *    @OA\Property(property="internal_user_id", type="int", format="internal_user_id", example=""),
 *    @OA\Property(property="card_data", type="string", format="card_data", example=""),
 *    @OA\Property(property="payment_data", type="string|null", format="payment_data", example=""),
 *    @OA\Property(property="source_data", type="string|null", format="source_data", example=""),
 *    @OA\Property(property="error_data", type="string|null", format="error_data", example=""),
 *    @OA\Property(property="status", type="string", format="status", example=""),
 *    @OA\Property(property="created_at", type="\Carbon\Carbon", format="created_at", example=""),
 *    
 * )
 */
class TransactionQueueItem extends Model
{
	protected $table = 'transaction_queue_items';
	public $timestamps = false;

	protected $casts = [
		'amount' => 'float',
		'internal_user_id' => 'int'
	];

	protected $fillable = [
		'type',
		'description',
		'amount',
		'currency',
		'internal_user_id',
		'card_data',
		'payment_data',
		'source_data',
		'error_data',
		'status'
	];
}
