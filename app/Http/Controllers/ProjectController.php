<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
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
}
