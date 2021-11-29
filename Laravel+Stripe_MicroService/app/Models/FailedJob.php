<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *    title="FailedJob",
 *    @OA\Property(property="id", type="int", format="id", example=""),
 *    @OA\Property(property="uuid", type="string", format="uuid", example=""),
 *    @OA\Property(property="connection", type="string", format="connection", example=""),
 *    @OA\Property(property="queue", type="string", format="queue", example=""),
 *    @OA\Property(property="payload", type="string", format="payload", example=""),
 *    @OA\Property(property="exception", type="string", format="exception", example=""),
 *    @OA\Property(property="failed_at", type="\Carbon\Carbon", format="failed_at", example=""),
 *    
 * )
 */
class FailedJob extends Model
{
	protected $table = 'failed_jobs';
	public $timestamps = false;

	protected $dates = [
		'failed_at'
	];

	protected $fillable = [
		'uuid',
		'connection',
		'queue',
		'payload',
		'exception',
		'failed_at'
	];
}
