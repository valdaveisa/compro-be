<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\Request;

class CommentController extends Controller
{

    public function index(Task $task)
    {
        return $task->comments()
            ->with('user:id,name,email')
            ->orderBy('created_at')
            ->get();
    }

    public function store(Request $request, Task $task)
    {
        $user = $request->user();

        $data = $request->validate([
            'content' => 'required|string',
        ]);

        $comment = Comment::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'content' => $data['content'],
        ]);

        // load relasi biar gampang dipakai di helper
        $comment->load(['user', 'task.project']);

        // panggil helper buat mention
        $this->handleMentions($comment);

        return response()->json(
            $comment->load('user:id,name,username,email'),
            201
        );
    }


    public function update(Request $request, Comment $comment)
    {
        $user = $request->user();

        // aturan hanya pemilik comment boleh edit
        if ($comment->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'content' => 'required|string',
        ]);

        $comment->update($data);

        return response()->json($comment->load('user:id,name,email'));
    }

    public function destroy(Request $request, Comment $comment)
    {
        $user = $request->user();

        if ($comment->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted']);
    }

    protected function handleMentions(Comment $comment): void
    {
        $task = $comment->task;
        $project = $task->project;
        $author = $comment->user;
        $content = $comment->content;

        // 1. Existing Username Logic (@username)
        preg_match_all('/@([A-Za-z0-9_\.]+)/', $content, $matches);
        $usernames = collect($matches[1] ?? [])->unique();

        $mentionedUserIds = collect();

        if ($usernames->isNotEmpty()) {
            $usersByUsername = User::whereIn('username', $usernames)->pluck('id');
            $mentionedUserIds = $mentionedUserIds->merge($usersByUsername);
        }

        // 2. Email Logic (Check if any project member's email is present in text)
        // This allows users to tag by typing the email, e.g. "hey user@example.com check this"
        // Get all potential users from the project or task
        $potentialUsers = $project ? $project->members : collect(); // Simple fetch, might be heavy for huge projects but fine here
        
        foreach ($potentialUsers as $u) {
            if ($u->id === $author->id) continue;
            
            // Check if email is in content (case insensitive)
            if (stripos($content, $u->email) !== false) {
                $mentionedUserIds->push($u->id);
            }
        }
        
        $mentionedUserIds = $mentionedUserIds->unique();

        foreach ($mentionedUserIds as $uid) {
            if ($uid === $author->id) continue;

            // Check if notif already exists for this comment to avoid dupes if edited (optional, skipping for simple store)
            
            UserNotification::create([
                'user_id'    => $uid,
                'type'       => 'comment_mention',
                'title'      => 'You were mentioned',
                'message'    => "{$author->name} mentioned you in task '{$task->title}'",
                'project_id' => $project->id ?? null,
                'task_id'    => $task->id,
            ]);
        }
    }

}
