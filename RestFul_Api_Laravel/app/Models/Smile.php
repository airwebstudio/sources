<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Smile extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'meeting_id', 'user_id', 'smile',
    ];

    public function meeting() {
        return $this->belongsTo(Meeting::class, 'meeting_id');
    }

    public function participant() {
        return $this->belongsTo(Participant::class, 'user_id');
    }
}
