<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingReport extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'meeting_id', 'user_id', 'type', 'names', 'description',
    ];

    public function meeting() {
        return $this->belongsTo(Meeting::class, 'meeting_id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
