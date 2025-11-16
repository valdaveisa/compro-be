<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // GET /api/tasks/{task}/comments
    public function index(Task $task)
    {
        return $task->comments()
            ->with('user:id,name,email')
            ->orderBy('created_at')
            ->get();
    }

    // POST /api/tasks/{task}/comments
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

    // PUT /api/comments/{comment}
    public function update(Request $request, Comment $comment)
    {
        $user = $request->user();

        // aturan sederhana: hanya pemilik comment boleh edit
        if ($comment->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'content' => 'required|string',
        ]);

        $comment->update($data);

        return response()->json($comment->load('user:id,name,email'));
    }

    // DELETE /api/comments/{comment}
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
        // cari pola @username di content
        preg_match_all('/@([A-Za-z0-9_\.]+)/', $comment->content, $matches);

        $usernames = collect($matches[1] ?? [])->unique();

        if ($usernames->isEmpty()) {
            return;
        }

        $task = $comment->task;
        $project = $task->project;
        $author = $comment->user;

        // cari user yang username-nya ada di daftar mention
        $mentionedUsers = User::whereIn('username', $usernames)->get();

        foreach ($mentionedUsers as $mentioned) {
            // jangan kirim notif ke diri sendiri
            if ($mentioned->id === $author->id) {
                continue;
            }

            UserNotification::create([
                'user_id'    => $mentioned->id,
                'type'       => 'comment_mention',
                'title'      => 'You were mentioned in a comment',
                'message'    => "{$author->name} (@{$author->username}) mentioned you in task '{$task->title}'",
                'project_id' => $project->id ?? null,
                'task_id'    => $task->id,
            ]);
        }
    }

}
