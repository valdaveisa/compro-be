<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Filter: If project_id selected, we filter by that project. 
        // Otherwise, show stats for ALL projects the user is involved in.
        
        $projectsQuery = Project::where('created_by', $user->id)
            ->orWhereHas('members', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
            
        $projects = $projectsQuery->get();
        
        $selectedProject = null;
        if ($request->has('project_id') && $request->project_id != '') {
            $selectedProject = $projects->find($request->project_id);
            // If project not found or access denied (though query limits to accessible), fallback to all.
        }

        // Stats Calculation
        // 1. Total Projects (or 1 if selected)
        $totalProjects = $selectedProject ? 1 : $projects->count();

        // 2. Completed On Time
        // Logic: Status done AND updated_at <= end_date
        $completedOnTimeQuery = clone $projectsQuery;
        if ($selectedProject) {
            $completedOnTimeCount = ($selectedProject->status == 'done' && $selectedProject->updated_at <= $selectedProject->end_date) ? 1 : 0;
        } else {
            $completedOnTimeCount = Project::whereIn('id', $projects->pluck('id'))
                ->where('status', 'done')
                ->whereColumn('updated_at', '<=', 'end_date')
                ->count();
        }
        
        // 3. Overdue Tasks
        $taskQuery = Task::whereIn('project_id', $projects->pluck('id'))
            ->where('status', '!=', 'done')
            ->where('due_date', '<', now());
            
        if ($selectedProject) {
            $taskQuery->where('project_id', $selectedProject->id);
        }
        $overdueTasksCount = $taskQuery->count();

        // 4. Burndown Rate (Done / Total)
        $allTasksQuery = Task::whereIn('project_id', $projects->pluck('id'));
        if ($selectedProject) $allTasksQuery->where('project_id', $selectedProject->id);
        
        $totalTasks = $allTasksQuery->count();
        $doneTasks = (clone $allTasksQuery)->where('status', 'done')->count();
        $burndownRate = $totalTasks > 0 ? round(($doneTasks / $totalTasks) * 100) : 0;


        // Charts Data
        
        // Chart 1: Workload (Active Tasks per User)
        // We need all users involved in these projects
        $workloadData = [];
        $activeTasksQuery = Task::whereIn('project_id', $projects->pluck('id'))
            ->where('status', '!=', 'done')
            ->whereNotNull('assignee_id');
            
        if ($selectedProject) $activeTasksQuery->where('project_id', $selectedProject->id);

        $workload = $activeTasksQuery->select('assignee_id', DB::raw('count(*) as count'))
            ->groupBy('assignee_id')
            ->with('assignee')
            ->get();
            
        $workloadLabels = [];
        $workloadValues = [];
        
        foreach($workload as $w) {
            $workloadLabels[] = $w->assignee->name ?? 'Unknown';
            $workloadValues[] = $w->count;
        }

        // Chart 2: Project Status Distribution (Only relevant for global view, but for single project it's just 100% one color)
        $statusLabels = ['planned', 'active', 'on_hold', 'done'];
        $statusValues = [0, 0, 0, 0];
        
        if ($selectedProject) {
            $idx = array_search($selectedProject->status, $statusLabels);
            if ($idx !== false) $statusValues[$idx] = 1;
        } else {
            $statusCounts = Project::whereIn('id', $projects->pluck('id'))
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')->toArray();
                
            foreach($statusLabels as $idx => $status) {
                $statusValues[$idx] = $statusCounts[$status] ?? 0;
            }
        }

        return view('reports.index', compact(
            'projects', 
            'selectedProject', 
            'totalProjects', 
            'completedOnTimeCount', 
            'overdueTasksCount', 
            'burndownRate',
            'workloadLabels',
            'workloadValues',
            'statusLabels',
            'statusValues'
        ));
    }
}
