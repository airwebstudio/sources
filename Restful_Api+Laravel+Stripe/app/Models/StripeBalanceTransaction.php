<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *    title="StripeBalanceTransaction",
 *    @OA\Property(property="id", type="int", format="id", example=""),
 *    @OA\Property(property="type", type="string", format="type", example=""),
 *    @OA\Property(property="internal_user_id", type="int|null", format="internal_user_id", example=""),
 *    @OA\Property(property="balance_transaction_id", type="string|null", format="balance_transaction_id", example=""),
 *    @OA\Property(property="status", type="string", format="status", example=""),
 *    @OA\Property(property="amount", type="int", format="amount", example=""),
 *    @OA\Property(property="fee", type="int", format="fee", example=""),
 *    @OA\Property(property="available_on", type="\Carbon\Carbon", format="available_on", example=""),
 *    @OA\Property(property="created_at", type="\Carbon\Carbon|null", format="created_at", example=""),
 *    @OA\Property(property="updated_at", type="\Carbon\Carbon|null", format="updated_at", example=""),
 *    
 * )
 */
class StripeBalanceTransaction extends Model
{
	protected $table = 'stripe_balance_transaction';

	protected $casts = [
		'internal_user_id' => 'int',
		'amount' => 'float',
		'fee' => 'float',
		'id' => 'string',
		'updated_at' => 'datetime:Y-m-d H:i:s',
		'available_on' => 'datetime:Y-m-d H:i:s',
	];

	protected $dates = [
		'available_on','updated_at'
	];

	protected $fillable = [
		'type',
		'internal_user_id',
		'balance_transaction_id',
		'status',
		'amount',
		'fee',
		'available_on',
	];
}
