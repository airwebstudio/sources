<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *    title="Account",
 *    @OA\Property(property="id", type="int", format="id", example=""),
 *    @OA\Property(property="internal_user_id", type="string", format="internal_user_id", example=""),
 *    @OA\Property(property="stripe_account_id", type="string", format="stripe_account_id", example=""),
 *    @OA\Property(property="verificated", type="bool|null", format="verificated", example=""),
 *    
 * )
 */
class Account extends Model
{
	protected $table = 'accounts';
	public $timestamps = false;

	protected $casts = [
		'verificated' => 'bool'
	];

	protected $fillable = [
		'internal_user_id',
		'stripe_account_id',
		'verificated'
	];
}
