<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\BaseModel;

class TimeEntry extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'started_at',
        'ended_at',
        'duration_minutes',
        'note',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
