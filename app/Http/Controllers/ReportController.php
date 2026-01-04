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

        // 2. Finished & Unfinished Metrics
        // Context depends on $selectedProject
        
        $finishedCount = 0;
        $unfinishedCount = 0;
        $finishedLabel1 = "";
        $unfinishedLabel2 = "";

        if ($selectedProject) {
            // SINGLE PROJECT VIEW: Metrics are about TASKS
            // Metric 1: Tugas Selesai
            $finishedCount = Task::where('project_id', $selectedProject->id)->where('status', 'done')->count();
            
            // Metric 2: Tugas Belum Selesai (Not done)
            $unfinishedCount = Task::where('project_id', $selectedProject->id)->where('status', '!=', 'done')->count();
            
        } else {
            // GLOBAL VIEW: Metrics are about PROJECTS
            // Metric 1: Proyek Selesai
            $finishedCount = Project::whereIn('id', $projects->pluck('id'))
                ->where('status', 'done')
                ->count();
                
            // Metric 2: Proyek Belum Selesai (Not done)
            $unfinishedCount = Project::whereIn('id', $projects->pluck('id'))
                ->where('status', '!=', 'done')
                ->count();
        }

        // 4. Burndown Rate (Done / Total)
        // Keep as is, works for both contexts (task level aggregation)
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

        // Chart 2: Status Distribution
        // If Single Project -> Task Statuses
        // If All Projects -> Project Statuses
        
        $chart2Title = "";
        $statusLabels = [];
        $statusValues = [];
        $statusColors = []; // Optional: if we want to map specific colors

        if ($selectedProject) {
            $chart2Title = "Status Tugas (Persentase)";
            $statusLabels = ['todo', 'in_progress', 'review', 'done'];
            // Normalized labels for display? Optional, view can handle casing.
            
            // Count tasks
            $taskCounts = Task::where('project_id', $selectedProject->id)
                 ->select('status', DB::raw('count(*) as count'))
                 ->groupBy('status')
                 ->pluck('count', 'status')
                 ->toArray();
                 
            foreach($statusLabels as $lbl) {
                $statusValues[] = $taskCounts[$lbl] ?? 0;
            }
        } else {
            $chart2Title = "Status Proyek (Persentase)";
            $statusLabels = ['planned', 'active', 'on_hold', 'done'];
            
            $statusCounts = Project::whereIn('id', $projects->pluck('id'))
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')->toArray();
                
            foreach($statusLabels as $lbl) {
                $statusValues[] = $statusCounts[$lbl] ?? 0;
            }
        }

        return view('reports.index', compact(
            'projects', 
            'selectedProject', 
            'totalProjects', 
            'finishedCount', 
            'unfinishedCount', 
            'burndownRate',
            'workloadLabels',
            'workloadValues',
            'statusLabels',
            'statusValues',
            'chart2Title'
        ));
    }

    public function export(Request $request) 
    {
        $user = $request->user();
        
        $projectsQuery = Project::where('created_by', $user->id)
            ->orWhereHas('members', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
            
        // Apply single project filter if present
        if ($request->has('project_id') && $request->project_id != '') {
            $projectsQuery->where('id', $request->project_id);
        }
            
        $projects = $projectsQuery->with(['members', 'tasks'])->get();

        $filename = "laporan-proyek-" . date('Y-m-d') . ".csv";
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($projects) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel UTF-8 recognition
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            fputcsv($file, ['ID', 'Nama Proyek', 'Status', 'Start Date', 'End Date', 'Total Tugas', 'Tugas Selesai', 'Burndown (%)', 'PM'], ';');

            foreach ($projects as $project) {
                $totalTasks = $project->tasks->count();
                $doneTasks = $project->tasks->where('status', 'done')->count();
                $burndown = $totalTasks > 0 ? round(($doneTasks / $totalTasks) * 100) : 0;
                
                // Find PM
                $pm = $project->members->where('pivot.role_in_project', 'pm')->first();
                $pmEmail = $pm ? $pm->email : '-';

                fputcsv($file, [
                    $project->id, 
                    $project->name, 
                    ucfirst($project->status), 
                    $project->start_date, 
                    $project->end_date, 
                    $totalTasks,
                    $doneTasks,
                    str_replace('.', ',', $burndown . '%'), // Ensure percentages use comma if needed, though % string is safe
                    $pmEmail
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
