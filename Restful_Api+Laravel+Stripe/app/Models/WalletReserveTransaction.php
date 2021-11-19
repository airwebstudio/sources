<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *    title="WalletReserveTransaction",
 *    @OA\Property(property="id", type="int", format="id", example=""),
 *    @OA\Property(property="seller_account_id", type="int", format="seller_account_id", example=""),
 *    @OA\Property(property="buyer_account_id", type="int", format="buyer_account_id", example=""),
 *    @OA\Property(property="meeting_hash", type="string", format="meeting_hash", example=""),
 *    @OA\Property(property="description", type="string|null", format="description", example=""),
 *    @OA\Property(property="expired_date", type="\Carbon\Carbon|null", format="expired_date", example=""),
 *    @OA\Property(property="amount", type="int", format="amount", example=""),
 *    @OA\Property(property="created_at", type="\Carbon\Carbon|null", format="created_at", example=""),
 *    @OA\Property(property="updated_at", type="\Carbon\Carbon|null", format="updated_at", example=""),
 *    
 * )
 */
class WalletReserveTransaction extends Model
{
	protected $table = 'wallet_reserve_transaction';

	protected $casts = [
		'seller_account_id' => 'int',
		'buyer_account_id' => 'int',
		'amount' => 'float',
		'created_at' => 'datetime:Y-m-d H:i:s',
		'updated_at' => 'datetime:Y-m-d H:i:s',
	];

	protected $dates = [
		'expired_date'
	];

	protected $fillable = [
		'seller_account_id',
		'buyer_account_id',
		'meeting_hash',
		'description',
		'expired_date',
		'amount'
	];
}
