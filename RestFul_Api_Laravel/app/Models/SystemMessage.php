<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemMessage extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'meeting_id', 'system_with_user_id', 'content', 'writed_at',
    ];

    public function meeting() {
        return $this->belongsTo(Meeting::class, 'meeting_id');
    }

    public function participant() {
        return $this->belongsTo(Participant::class, 'system_with_user_id');
    }
}
