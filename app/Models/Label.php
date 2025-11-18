<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\BaseModel;

class Label extends BaseModel
{
    use HasFactory;

    protected $fillable = ['name', 'color'];

    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'task_labels')
                    ->withTimestamps();
    }
}
