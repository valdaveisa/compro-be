<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Models\Label;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    // GET /api/projects/{project}/tasks
    public function index(Request $request, Project $project)
    {
        // nanti FE bisa pakai ini untuk kanban/gantt/calendar
        $tasks = Task::with(['assignee', 'labels', 'subtasks'])
            ->where('project_id', $project->id)
            ->orderBy('due_date')
            ->get();

        return response()->json($tasks);
    }

    // POST /api/projects/{project}/tasks
    public function store(Request $request, Project $project)
    {
        $user = $request->user();

        $data = $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'status'        => 'nullable|in:todo,in_progress,review,done',
            'priority'      => 'nullable|in:low,medium,high',
            'start_date'    => 'nullable|date',
            'due_date'      => 'nullable|date|after_or_equal:start_date',
            'assignee_id'   => 'nullable|exists:users,id',
            'parent_task_id'=> 'nullable|exists:tasks,id',
        ]);


        $data['status']       = $data['status'] ?? 'todo';
        $data['priority']     = $data['priority'] ?? 'medium';
        $data['project_id']   = $project->id;
        $data['created_by']   = $user->id;

        $task = Task::create($data);

        return response()->json($task, 201);
    }

    // GET /api/tasks/{task}
    public function show(Task $task)
    {
        $task->load(['project', 'assignee', 'labels', 'subtasks']);

        return response()->json($task);
    }

    // PUT /api/tasks/{task}
    public function update(Request $request, Task $task)
    {
        $data = $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'status'        => 'nullable|in:todo,in_progress,review,done',
            'priority'      => 'nullable|in:low,medium,high',
            'start_date'    => 'nullable|date',
            'due_date'      => 'nullable|date|after_or_equal:start_date',
            'assignee_id'   => 'nullable|exists:users,id',
            'parent_task_id'=> 'nullable|exists:tasks,id',
        ]);


        $task->update($data);

        return response()->json($task);
    }

    // DELETE /api/tasks/{task}
    public function destroy(Task $task)
    {
        $task->delete();

        return response()->json(['message' => 'Task deleted']);
    }

    // PATCH /api/tasks/{task}/status
    public function updateStatus(Request $request, Task $task)
    {
        $data = $request->validate([
            'status' => 'required|in:todo,in_progress,review,done',
        ]);

        $update = ['status' => $data['status']];

        if ($data['status'] === 'done' && is_null($task->completed_at)) {
            $update['completed_at'] = now();
        }

        if ($data['status'] !== 'done') {
            $update['completed_at'] = null;
        }

        $task->update($update);

        return response()->json($task);
    }


    // PATCH /api/tasks/{task}/assign
    public function assignUser(Request $request, Task $task)
    {
        $data = $request->validate([
            'assignee_id' => 'required|exists:users,id',
        ]);

        $task->update(['assignee_id' => $data['assignee_id']]);

        return response()->json($task);
    }

    // POST /api/tasks/{task}/labels
    public function attachLabel(Request $request, Task $task)
    {
        $data = $request->validate([
            'label_id' => 'required|exists:labels,id',
        ]);

        $task->labels()->syncWithoutDetaching([$data['label_id']]);

        return response()->json(['message' => 'Label attached']);
    }

    // DELETE /api/tasks/{task}/labels/{label}
    public function detachLabel(Task $task, Label $label)
    {
        $task->labels()->detach($label->id);

        return response()->json(['message' => 'Label detached']);
    }
}
