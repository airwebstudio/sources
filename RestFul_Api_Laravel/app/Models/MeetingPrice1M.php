<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *    title="MeetingPrice1M",
 *    @OA\Property(property="id", type="int", format="id", example=""),
 *    @OA\Property(property="meeting_hash", type="string", format="meeting_hash", example=""),
 *    @OA\Property(property="seller_account_id", type="int", format="seller_account_id", example=""),
 *    @OA\Property(property="price", type="int", format="price", example=""),
 *    @OA\Property(property="created_at", type="\Carbon\Carbon|null", format="created_at", example=""),
 *    @OA\Property(property="updated_at", type="\Carbon\Carbon|null", format="updated_at", example=""),
 *    
 * )
 */
class MeetingPrice1M extends Model
{
	protected $table = 'meeting_price_1_m_fixed';

	protected $casts = [
		'seller_account_id' => 'int',
		'price' => 'int'
	];

	protected $fillable = [
		'meeting_hash',
		'seller_account_id',
		'price',
		'is_dynamic_price',
	];

	public function dprices()
    {
        return $this->hasMany(MeetingDynamicalPrice::class, 'meeting_hash', 'meeting_hash');
    }
}
