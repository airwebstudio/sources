<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Schema(
 *     title="StorageFile",
 *     @OA\Property(property="id", type="string", format="id", example="1"),
 *     @OA\Property(property="name", type="string", format="name", example="name.png"),
 *     @OA\Property(property="user_id", type="string", format="user_id", example="1"),
 *     @OA\Property(property="meeting_hash", type="string", format="meeting_hash", example="dd828553-075e-4204-a7fb-ae6896f8f7e1"),
 *     @OA\Property(property="unique_key", type="string", format="unique_key", example="Rb1kIpB7AyVaCPwTD2YT2S8lcZB3M49meIK2RWit.png"),
 *     @OA\Property(property="mime_type", type="string", format="mime_type", example="image/png"),
 *     @OA\Property(property="filesize", type="string", format="filesize", example="27751"),
 *     @OA\Property(property="url_on_storage", type="string", format="url_on_storage", example="2021/03/31/Rb1kIpB7AyVaCPwTD2YT2S8lcZB3M49meIK2RWit.png"),
 *     @OA\Property(property="created_at", type="string", format="created_at", example="2021-03-31 21:57:14"),
 *     @OA\Property(property="updated_at", type="string", format="updated_at", example="2021-03-31 21:57:14"),
 * )
 */
class StorageFile extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'user_id',
        'meeting_hash',
        'unique_key',
        'mime_type',
        'filesize',
        'url_on_storage'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'storage';

    protected $appends = [
        'storage_url'
    ];

    public function getStorageUrlAttribute(){
        return getenv("STORAGE_URL").'/'.$this->url_on_storage;
    }


    public function meetings()
    {
        return $this->belongsToMany(Meeting::class, 'meeting_attchments', 'storage_id','meeting_hash', 'id','hash');
    }
    /**
     * full delete from storage
     */
    public function delete_storage(): bool
    {
        $this->meetings()->detach();
        if($this->delete() && Storage::delete($this->url_on_storage)){
            return true;
        }else{
            return false;
        }
    }
}
