<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @OA\Schema(
 *     title="Page",
 *     @OA\Property(property="id", type="string", format="id", example="1"),
 *     @OA\Property(property="title", type="string", format="title", example="title"),
 *     @OA\Property(property="description", type="string", format="description", example="description"),
 *     @OA\Property(property="content", type="string", format="content", example="content"),
 *     @OA\Property(property="slug", type="string", format="slug", example="slug"),
 *     @OA\Property(property="created_at", type="string", format="created_at", example="2021-03-18T09:49:02.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="updated_at", example="2021-03-18T09:49:02.000000Z"),
 * )
 */
class Page extends Model implements JWTSubject
{
    use HasFactory;

    protected $fillable = ['slug', 'title', 'description', 'content'];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
