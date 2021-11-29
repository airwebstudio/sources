<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'meeting_id', 'user_id', 'text', 'status', 'uid', 'started_at', 'finished_at',
    ];

    public function meeting() {
        return $this->belongsTo(Meeting::class, 'meeting_id');
    }

    public function participant() {
        return $this->belongsTo(Participant::class, 'system_with_user_id');
    }

    public function votes() {
        return $this->belongsToMany(User::class, 'users_questions', 'question_id', 'user_id');
    }
}
