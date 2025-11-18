<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\BaseModel;

class Attachment extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'filename',
        'path',
        'mime_type',
        'size'
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
