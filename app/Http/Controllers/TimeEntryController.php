<?php

namespace App\Http\Controllers;

use App\Models\TimeEntry;
use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TimeEntryController extends Controller
{
    // GET /api/tasks/{task}/time-entries
    public function index(Task $task)
    {
        return $task->timeEntries()
            ->with('user:id,name,email')
            ->orderBy('started_at', 'desc')
            ->get();
    }

    // POST /api/tasks/{task}/time-entries
    // manual input (misal kalau lupa start timer)
    public function store(Request $request, Task $task)
    {
        $user = $request->user();

        $data = $request->validate([
            'started_at' => 'required|date',
            'ended_at'   => 'required|date|after:started_at',
            'note'       => 'nullable|string',
        ]);

        $start = Carbon::parse($data['started_at']);
        $end   = Carbon::parse($data['ended_at']);

        $durationMinutes = $start->diffInMinutes($end);

        $entry = TimeEntry::create([
            'task_id'         => $task->id,
            'user_id'         => $user->id,
            'started_at'      => $start,
            'ended_at'        => $end,
            'duration_minutes'=> $durationMinutes,
            'note'            => $data['note'] ?? null,
        ]);

        return response()->json($entry->load('user:id,name,email'), 201);
    }

    // POST /api/tasks/{task}/time-entries/start
    public function start(Request $request, Task $task)
    {
        $user = $request->user();

        // cek apakah masih ada timer yg belum di-stop untuk user ini
        $running = TimeEntry::where('user_id', $user->id)
            ->whereNull('ended_at')
            ->first();

        if ($running) {
            return response()->json([
                'message' => 'Masih ada timer yang berjalan. Stop dulu sebelum start baru.'
            ], 422);
        }

        $entry = TimeEntry::create([
            'task_id'         => $task->id,
            'user_id'         => $user->id,
            'started_at'      => now(),
            'ended_at'        => null,
            'duration_minutes'=> null,
            'note'            => $request->input('note'),
        ]);

        return response()->json($entry, 201);
    }

    // POST /api/tasks/{task}/time-entries/stop
    public function stop(Request $request, Task $task)
    {
        $user = $request->user();

        $entry = TimeEntry::where('task_id', $task->id)
            ->where('user_id', $user->id)
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();

        if (! $entry) {
            return response()->json([
                'message' => 'Tidak ada timer berjalan untuk task ini.'
            ], 404);
        }

        $end = now();
        $durationMinutes = $entry->started_at->diffInMinutes($end);

        $entry->update([
            'ended_at'        => $end,
            'duration_minutes'=> $durationMinutes,
        ]);

        return response()->json($entry->fresh()->load('user:id,name,email'));
    }

    // (opsional) summary per project
    // GET /api/projects/{project}/time-report
    public function projectReport(Project $project)
    {
        $summary = TimeEntry::selectRaw('user_id, SUM(duration_minutes) as total_minutes')
            ->whereHas('task', function ($q) use ($project) {
                $q->where('project_id', $project->id);
            })
            ->whereNotNull('duration_minutes')
            ->groupBy('user_id')
            ->with('user:id,name,email')
            ->get();

        return response()->json($summary);
    }
}
