<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Models\Task;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    // GET /api/projects
    public function index(Request $request)
    {
        $user = $request->user();

        $projects = Project::withCount([
                'tasks as total_tasks',
                'tasks as done_tasks' => function ($q) {
                    $q->where('status', 'done');
                },
            ])
            ->where('created_by', $user->id)
            ->orWhereHas('members', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })
            ->get();

        return response()->json($projects);
    }

    // POST /api/projects
    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
        ]);

        $data['status']     = 'planned';
        $data['created_by'] = $user->id;

        $project = Project::create($data);

        // otomatis jadikan creator sebagai PM di project
        $project->members()->attach($user->id, [
            'role_in_project' => 'pm',
        ]);

        return response()->json($project, 201);
    }

    // GET /api/projects/{project}
    public function show(Request $request, Project $project)
    {
        $project->load(['members', 'tasks']);

        return response()->json($project);
    }

    // PUT /api/projects/{project}
    public function update(Request $request, Project $project)
    {
        $user = $request->user();

        // aturan simpel: hanya creator yang boleh update
        if ($project->created_by !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'name'        => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'status'      => 'nullable|in:planned,active,on_hold,done',
        ]);

        $project->update($data);

        return response()->json($project);
    }

    // DELETE /api/projects/{project}
    public function destroy(Request $request, Project $project)
    {
        $user = $request->user();

        if ($project->created_by !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $project->delete();

        return response()->json(['message' => 'Project deleted']);
    }

    // POST /api/projects/{project}/members
    public function addMember(Request $request, Project $project)
    {
        $user = $request->user();

        // hanya creator yang boleh manage member (sederhana dulu)
        if ($project->created_by !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'user_id'        => 'required|exists:users,id',
            'role_in_project'=> 'required|in:pm,member,qa,writer',
        ]);

        $project->members()->syncWithoutDetaching([
            $data['user_id'] => ['role_in_project' => $data['role_in_project']],
        ]);

        return response()->json(['message' => 'Member added/updated']);
    }

    // DELETE /api/projects/{project}/members/{user}
    public function removeMember(Request $request, Project $project, User $user)
    {
        $current = $request->user();

        if ($project->created_by !== $current->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $project->members()->detach($user->id);

        return response()->json(['message' => 'Member removed']);
    }

    // GET /api/projects/{project}/kanban
    public function kanban(Project $project)
    {
        $tasks = Task::with('assignee:id,name')
            ->where('project_id', $project->id)
            ->orderByRaw("FIELD(priority, 'high','medium','low')")
            ->orderBy('due_date')
            ->get();

        $grouped = [
            'todo'        => $tasks->where('status', 'todo')->values(),
            'in_progress' => $tasks->where('status', 'in_progress')->values(),
            'review'      => $tasks->where('status', 'review')->values(),
            'done'        => $tasks->where('status', 'done')->values(),
        ];

        return response()->json($grouped);
    }

    // GET /api/projects/{project}/calendar?start=2025-11-01&end=2025-11-30
    public function calendar(Request $request, Project $project)
    {
        $start = $request->query('start')
            ? Carbon::parse($request->query('start'))->startOfDay()
            : now()->startOfMonth();

        $end = $request->query('end')
            ? Carbon::parse($request->query('end'))->endOfDay()
            : now()->endOfMonth();

        $tasks = Task::where('project_id', $project->id)
            ->whereBetween('due_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('due_date')
            ->get(['id', 'title', 'due_date', 'status', 'priority']);

        $events = $tasks->map(function ($task) {
            return [
                'id'       => $task->id,
                'title'    => $task->title,
                'date'     => $task->due_date,
                'status'   => $task->status,
                'priority' => $task->priority,
                'type'     => 'task',
            ];
        });

        return response()->json($events);
    }

    // GET /api/projects/{project}/gantt
    public function gantt(Project $project)
    {
        $tasks = Task::with('assignee:id,name')
            ->where('project_id', $project->id)
            ->whereNotNull('due_date')
            ->orderBy('start_date')
            ->get();

        $items = $tasks->map(function ($task) {
            return [
                'id'        => $task->id,
                'title'     => $task->title,
                'start'     => $task->start_date
                                    ? $task->start_date->toDateString()
                                    : $task->created_at->toDateString(),
                'end'       => $task->due_date->toDateString(),
                'status'    => $task->status,
                'assignee'  => $task->assignee ? [
                    'id'   => $task->assignee->id,
                    'name' => $task->assignee->name,
                ] : null,
            ];
        });

        return response()->json($items);
    }

    // GET /api/projects/{project}/stats
    public function stats(Project $project)
    {
        $tasks = $project->tasks()->get();

        $total      = $tasks->count();
        $done       = $tasks->where('status', 'done')->count();
        $byStatus   = $tasks->groupBy('status')->map->count();

        $today = now()->toDateString();
        $overdue = $tasks
            ->where('status', '!=', 'done')
            ->where('due_date', '<', $today)
            ->count();

        $completionRate = $total > 0 ? round($done / $total * 100, 1) : 0;

        return response()->json([
            'total_tasks'      => $total,
            'done_tasks'       => $done,
            'completion_rate'  => $completionRate,    // %
            'overdue_tasks'    => $overdue,
            'tasks_by_status'  => $byStatus,
        ]);
    }

    // GET /api/projects/{project}/member-performance
    public function memberPerformance(Project $project)
    {
        $members = $project->members; // user2 user3 dst

        $data = $members->map(function ($user) use ($project) {
            $tasksQuery = Task::where('project_id', $project->id)
                ->where('assignee_id', $user->id);

            $assigned = (clone $tasksQuery)->count();
            $done     = (clone $tasksQuery)->where('status', 'done')->count();

            $durations = (clone $tasksQuery)
                ->whereNotNull('completed_at')
                ->get()
                ->map(fn ($task) => $task->created_at->diffInDays($task->completed_at));

            $avgDays = $durations->count() ? round($durations->avg(), 1) : null;

            return [
                'user' => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                ],
                'tasks_assigned'       => $assigned,
                'tasks_done'           => $done,
                'done_ratio'           => $assigned > 0 ? round($done / $assigned * 100, 1) : 0,
                'avg_completion_days'  => $avgDays,
            ];
        });

        return response()->json($data);
    }


}
