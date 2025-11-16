<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    // List attachment per task
    public function index(Task $task)
    {
        return $task->attachments()->with('user:id,name')->get();
    }

    // Upload file ke task
    public function store(Request $request, Task $task)
    {
        $request->validate([
            'file' => 'required|file|max:5120', // max 5MB
        ]);

        $file = $request->file('file');

        $path = $file->store('attachments');

        $attachment = Attachment::create([
            'task_id'   => $task->id,
            'user_id'   => $request->user()->id,
            'filename'  => $file->getClientOriginalName(),
            'path'      => $path,
            'mime_type' => $file->getMimeType(),
            'size'      => $file->getSize(),
        ]);

        return response()->json($attachment, 201);
    }

    // Hapus attachment
    public function destroy(Attachment $attachment)
    {
        Storage::delete($attachment->path);
        $attachment->delete();

        return response()->json(['message' => 'Attachment deleted']);
    }
}
