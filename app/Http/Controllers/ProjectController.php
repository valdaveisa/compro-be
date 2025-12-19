<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Models\Task;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Store a newly created project in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'status'      => 'nullable|in:planned,active,on_hold,done',
        ]);

        $data['status']     = $data['status'] ?? 'planned';
        $data['created_by'] = $user->id;

        $project = Project::create($data);

        // Auto-assign creator as PM
        $project->members()->attach($user->id, [
            'role_in_project' => 'pm',
        ]);

        \App\Models\ActivityLog::create([
            'user_id' => $user->id,
            'subject_type' => Project::class,
            'subject_id' => $project->id, // Fixed: use project->id
            'action' => 'created project',
            'description' => "Created project '{$project->name}'",
        ]);

        // Handle Members (Array of IDs)
        if ($request->filled('members') && is_array($request->members)) {
            $memberIds = $request->members;
            // Attach members
            foreach ($memberIds as $memberId) {
                if ($memberId != $user->id) { // Avoid duplicating PM
                    $project->members()->attach($memberId, ['role_in_project' => 'member']);
                    
                    // Notify new member
                    \App\Models\UserNotification::create([
                        'user_id' => $memberId,
                        'type' => 'project_invite',
                        'title' => 'Project Invitation',
                        'message' => "You have been added to project '{$project->name}' by {$user->name}",
                        'project_id' => $project->id
                    ]);

                     // Log Member Add
                     $memberUser = User::find($memberId);
                     \App\Models\ActivityLog::create([
                        'user_id' => $user->id,
                        'subject_type' => Project::class,
                        'subject_id' => $project->id, 
                        'action' => 'added member',
                        'description' => "Added member '{$memberUser->name}' to project '{$project->name}'",
                    ]);
                }
            }
        }

        return redirect()->route('dashboard', ['project_id' => $project->id])
            ->with('success', 'Project created successfully.');
    }

    /**
     * Update the specified project in storage.
     */
    public function update(Request $request, Project $project)
    {
        $user = $request->user();

        if ($project->created_by !== $user->id) {
            abort(403);
        }

        $data = $request->validate([
            'name'        => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'status'      => 'nullable|in:planned,active,on_hold,done',
            'members'     => 'nullable|array',
            'members.*'   => 'exists:users,id'
        ]);

        $project->update($data);

        // Handle Members Update
        if ($request->has('members') && is_array($request->members)) {
            $memberIds = $request->members;
            
            // Ensure Owner is always there
            if (!in_array($project->created_by, $memberIds)) {
                $memberIds[] = $project->created_by;
            }

            $syncData = [];
            foreach ($memberIds as $uid) {
                $role = ($uid == $project->created_by) ? 'pm' : 'member';
                $syncData[$uid] = ['role_in_project' => $role];
            }
            
            $changes = $project->members()->sync($syncData);
            
            // Notify attached users
            foreach ($changes['attached'] as $attachedId) {
                if ($attachedId != $user->id) {
                    \App\Models\UserNotification::create([
                        'user_id' => $attachedId,
                        'type' => 'project_invite',
                        'title' => 'Project Invitation',
                        'message' => "You have been added to project '{$project->name}' by {$user->name}",
                        'project_id' => $project->id
                    ]);
                     // Log Member Add
                     $memberUser = User::find($attachedId); // Optimization: Fetch all at once if needed
                     if($memberUser){
                        \App\Models\ActivityLog::create([
                            'user_id' => $user->id,
                            'subject_type' => Project::class,
                            'subject_id' => $project->id, 
                            'action' => 'added member',
                            'description' => "Added member '{$memberUser->name}' to project",
                        ]);
                     }
                }
            }

        } else {
             // If members field is present but empty (cleared selection), we might want to remove everyone except PM. 
             // But if specific partial update logic is needed it might differ. 
             // Assuming if 'members' is sent as empty array, we sync to just PM.
             if ($request->has('members')) {
                 $project->members()->sync([
                     $project->created_by => ['role_in_project' => 'pm']
                 ]);
             }
        }

        return redirect()->route('dashboard', ['project_id' => $project->id])
            ->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the specified project from storage.
     */
    public function destroy(Request $request, Project $project)
    {
        $user = $request->user();

        if ($project->created_by !== $user->id) {
            return redirect()->back()->with('error', 'Hanya pembuat proyek yang dapat menghapus proyek ini.');
        }

        $project->delete();

        return redirect()->route('dashboard')
            ->with('success', 'Project deleted successfully.');
    }

    public function showVisualize(Request $request, Project $project)
    {
        $user = $request->user();
        
        // Ensure user has access (Creator or Member)
        $isMember = $project->members()->where('users.id', $user->id)->exists();
        if ($project->created_by !== $user->id && !$isMember) {
            abort(403);
        }

        $project->load(['tasks.assignee', 'members']);

        return view('project_views', compact('project'));
    }

    public function activities(Request $request, Project $project)
    {
        // Simple authorization
        $user = $request->user();
        $isMember = $project->members()->where('users.id', $user->id)->exists();
        if ($project->created_by !== $user->id && !$isMember) abort(403);

        $taskIds = $project->tasks()->pluck('id');

        $logs = \App\Models\ActivityLog::with('user')
            ->where(function($q) use ($project, $taskIds) {
                $q->where(function($q2) use ($project) {
                    $q2->where('subject_type', Project::class)
                       ->where('subject_id', $project->id);
                })->orWhere(function($q2) use ($taskIds) {
                    $q2->where('subject_type', Task::class)
                       ->whereIn('subject_id', $taskIds);
                });
            })
            ->latest()
            ->take(50)
            ->get();

        return response()->json($logs);
    }
}


