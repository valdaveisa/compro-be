<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Task;
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

        return response()->json(
            $comment->load('user:id,name,email'),
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
}
