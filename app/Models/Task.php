<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\BaseModel;

class Task extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'status',
        'priority',
        'progress',
        'start_date',       
        'due_date',
        'created_by',
        'assignee_id',
        'parent_task_id',
        'completed_at'
    ];

    protected $casts = [
    'start_date'    => 'date',
    'due_date'      => 'date',
    'completed_at'  => 'datetime',
];


    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function parent()
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    public function subtasks()
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }

    public function labels()
    {
        return $this->belongsToMany(Label::class, 'task_labels')
                    ->withTimestamps();
    }
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function attachments()
    {
    return $this->hasMany(Attachment::class);
    }
    public function timeEntries()
    {
        return $this->hasMany(TimeEntry::class);
    }


}

