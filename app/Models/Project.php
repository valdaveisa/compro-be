<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\BaseModel;
class Project extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'start_date', 'end_date', 'status', 'created_by',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'project_members')
                    ->withPivot('role_in_project')
                    ->withTimestamps();
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}

