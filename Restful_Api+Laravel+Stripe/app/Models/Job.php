<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *    title="Job",
 *    @OA\Property(property="id", type="int", format="id", example=""),
 *    @OA\Property(property="queue", type="string", format="queue", example=""),
 *    @OA\Property(property="payload", type="string", format="payload", example=""),
 *    @OA\Property(property="attempts", type="int", format="attempts", example=""),
 *    @OA\Property(property="reserved_at", type="int|null", format="reserved_at", example=""),
 *    @OA\Property(property="available_at", type="int", format="available_at", example=""),
 *    @OA\Property(property="created_at", type="int", format="created_at", example=""),
 *    
 * )
 */
class Job extends Model
{
	protected $table = 'jobs';
	public $timestamps = false;

	protected $casts = [
		'attempts' => 'int',
		'reserved_at' => 'int',
		'available_at' => 'int',
		'created_at' => 'int'
	];

	protected $fillable = [
		'queue',
		'payload',
		'attempts',
		'reserved_at',
		'available_at'
	];
}
