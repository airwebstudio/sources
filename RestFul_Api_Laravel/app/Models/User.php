<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @OA\Schema(
 *     title="User",
 *     @OA\Property(property="id", type="string", format="id", example="1"),
 *     @OA\Property(property="name", type="string", format="name", example="name"),
 *     @OA\Property(property="email", type="string", format="email", example="name@mail.com"),
 *     @OA\Property(property="email_verified_at", type="string", format="email_verified_at", example="2021-03-31 21:57:14"),
 *     @OA\Property(property="avatar_id", type="string", format="avatar_id", example="21"),
 *     @OA\Property(property="created_at", type="string", format="created_at", example="2021-03-18T09:49:02.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="updated_at", example="2021-03-18T09:49:02.000000Z"),
 * )
 */
class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_path',
        'is_seller',
        'description',
        'google_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'count_meetings'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

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

    public function seller()
    {
        return $this->hasOne(Seller::class);
    }

    public function storage()
    {
        return $this->hasMany(StorageFile::class);
    }

    public function avatar()
    {
        return $this->belongsTo(StorageFile::class, 'avatar_id');
    }

    public function meetings()
    {
        return $this->hasMany(Meeting::class, 'user_id');
    }

    public function meetingReports()
    {
        return $this->hasMany(MeetingReport::class);
    }

    public function getCountMeetingsAttribute()
    {
        return $this->meetings()->count();
    }

    public function MeetingAvailability()
    {
        return $this->hasMany(MeetingAvailability::class, 'user_id');
    }

    public function MyMeetingParticipant()
    {
        return $this->hasMany(MeetingParticipant::class, 'user_id');
    }

    public function UserFeedItems()
    {
        return $this->hasMany(UserFeedItem::class, 'user_id');
    }

    public function votes() {
        return $this->belongsToMany(Question::class, 'users_questions', 'user_id', 'question_id');
    }
}
