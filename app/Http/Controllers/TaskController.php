<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Models\Label;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Store a newly created task in storage.
     */
    public function store(Request $request, Project $project)
    {
        $user = $request->user();

        $data = $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'status'        => 'nullable|in:todo,in_progress,review,done',
            'priority'      => 'nullable|in:low,medium,high',
            'progress'      => 'nullable|integer|between:0,100',
            'parent_task_id'=> 'nullable|exists:tasks,id',
            'start_date'    => 'nullable|date',
            'due_date'      => 'nullable|date|after_or_equal:start_date',
            'assignee_id'   => 'nullable|exists:users,id',
        ]);

        $data['status']       = $data['status'] ?? 'todo';
        $data['priority']     = $data['priority'] ?? 'medium';
        $data['project_id']   = $project->id;
        $data['created_by']   = $user->id;
        $data['assignee_id']  = $data['assignee_id'] ?? $user->id; 

        $task = Task::create($data);

        // Log Activity
        \App\Models\ActivityLog::create([
            'user_id' => $user->id,
            'subject_type' => Task::class,
            'subject_id' => $task->id,
            'action' => 'created task',
            'description' => "Created task '{$task->title}' in project '{$project->name}'",
        ]);

        return redirect()->route('dashboard', ['project_id' => $project->id])
            ->with('success', 'Task added successfully.');
    }

    public function update(Request $request, Task $task)
    {
        $data = $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'status'        => 'nullable|in:todo,in_progress,review,done',
            'priority'      => 'nullable|in:low,medium,high',
            'progress'      => 'nullable|integer|between:0,100',
            'parent_task_id'=> 'nullable|exists:tasks,id',
            'start_date'    => 'nullable|date',
            'due_date'      => 'nullable|date|after_or_equal:start_date',
            'assignee_id'   => 'nullable|exists:users,id',
        ]);

        $oldStatus = $task->status;
        $task->update($data);

        // Log Status Change
        if ($oldStatus !== $task->status) {
             \App\Models\ActivityLog::create([
                'user_id' => $request->user()->id,
                'subject_type' => Task::class,
                'subject_id' => $task->id,
                'action' => 'updated status',
                'description' => "Updated status of task '{$task->title}' from {$oldStatus} to {$task->status}",
            ]);
        } else {
             \App\Models\ActivityLog::create([
                'user_id' => $request->user()->id,
                'subject_type' => Task::class,
                'subject_id' => $task->id,
                'action' => 'updated task',
                'description' => "Updated task details for '{$task->title}'",
            ]);
        }

        return redirect()->route('dashboard', ['project_id' => $task->project_id])
            ->with('success', 'Task updated successfully.');
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(Task $task)
    {
        $projectId = $task->project_id;
        $task->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Task deleted successfully.']);
        }

        return redirect()->route('dashboard', ['project_id' => $projectId])
            ->with('success', 'Task deleted successfully.');
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

    public function show(Task $task)
    {
        $task->load(['subtasks.assignee', 'assignee', 'comments.user', 'attachments', 'timeEntries', 'parent']);
        return response()->json($task);
    }
}
