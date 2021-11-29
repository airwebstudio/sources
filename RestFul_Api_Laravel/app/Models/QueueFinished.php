<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *    title="QueueFinished",
 *    @OA\Property(property="id", type="int", format="id", example=""),
 *    @OA\Property(property="qid", type="int", format="qid", example=""),
 *    @OA\Property(property="type", type="string", format="type", example=""),
 *    @OA\Property(property="status", type="string", format="status", example=""),
 *    @OA\Property(property="data", type="string", format="data", example=""),
 *    @OA\Property(property="created_at", type="\Carbon\Carbon|null", format="created_at", example=""),
 *    @OA\Property(property="updated_at", type="\Carbon\Carbon|null", format="updated_at", example=""),
 *    
 * )
 */
class QueueFinished extends Model
{
	protected $table = 'queue_finished';

	protected $casts = [
		'qid' => 'int'
	];

	protected $fillable = [
		'qid',
		'type',
		'status',
		'data'
	];
}
