<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TimeEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TimeEntryController extends Controller
{
    // POST /api/tasks/{task}/timer/start
    public function start(Request $request, Task $task)
    {
        $user = $request->user();

        // Check if there's already a running timer for this user on this task
        $running = TimeEntry::where('user_id', $user->id)
            ->where('task_id', $task->id)
            ->whereNull('ended_at')
            ->first();

        if ($running) {
            return response()->json(['message' => 'Timer already running'], 400);
        }

        // Stop any other running timers for this user? (Optional, but good practice)
        // For now, let's allow parallel timers or just one. Let's strict to one per user globally.
        $anyRunning = TimeEntry::where('user_id', $user->id)->whereNull('ended_at')->first();
        if ($anyRunning) {
             // Optional: Auto-stop previous
             // $anyRunning->update(['ended_at' => now(), 'duration_minutes' => ...]);
        }

        $entry = TimeEntry::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'started_at' => now(),
        ]);

        return response()->json($entry);
    }

    // POST /api/tasks/{task}/timer/stop
    public function stop(Request $request, Task $task)
    {
        $user = $request->user();

        $entry = TimeEntry::where('user_id', $user->id)
            ->where('task_id', $task->id)
            ->whereNull('ended_at')
            ->latest()
            ->first();

        if (!$entry) {
            return response()->json(['message' => 'No running timer found'], 404);
        }

        $end = now();
        $start = Carbon::parse($entry->started_at);
        $duration = $end->diffInMinutes($start);

        $entry->update([
            'ended_at' => $end,
            'duration_minutes' => $duration
        ]);

        return response()->json($entry);
    }

    public function index(Task $task)
    {
        return response()->json($task->timeEntries()->with('user')->latest()->get());
    }
}
