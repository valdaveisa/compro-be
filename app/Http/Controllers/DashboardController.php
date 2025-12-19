<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get all projects for the sidebar/list
        $projects = Project::where('created_by', $user->id)
            ->orWhereHas('members', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })
            ->withCount(['tasks', 'tasks as done_tasks_count' => function ($query) {
                $query->where('status', 'done');
            }])
            ->get();

        $selectedProject = null;
        $stats = [
            'total_projects' => $projects->count(),
            'active_projects' => $projects->where('status', 'active')->count(),
            'completed_projects' => $projects->where('status', 'done')->count(),
        ];

        // If a project is selected
        if ($request->has('project_id')) {
            $selectedProject = $projects->find($request->project_id);
            
            if ($selectedProject) {
                // Load detailed relationships for the selected project
                $selectedProject->load([
                    'tasks.assignee', 
                    'tasks.project', // useful if individual task display moves out of context
                    'members'
                ]);
            }
        }


        $allUsers = \App\Models\User::select('id', 'name', 'email')->orderBy('name')->get();
        $unreadNotificationsCount = $user->notificationsCustom()->where('is_read', false)->count();

        return view('dashboard', compact('projects', 'selectedProject', 'stats', 'allUsers', 'unreadNotificationsCount'));
    }
}
