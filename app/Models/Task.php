<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'title', 'description',
        'status', 'priority', 'due_date',
        'created_by', 'assignee_id', 'parent_task_id',
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

