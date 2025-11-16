<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\TimeEntryController;


// ================== AUTH API ==================

// /api/register  (prefix /api datang otomatis, jadi DI SINI cukup /register)
Route::post('/register', function (Request $request) {
    $validated = $request->validate([
        'name'                  => 'required|string|max:255',
        'email'                 => 'required|email|unique:users,email',
        'password'              => 'required|string|min:8|confirmed',
    ]);

    $user = User::create([
        'name'     => $validated['name'],
        'email'    => $validated['email'],
        'password' => Hash::make($validated['password']),
    ]);

    $token = $user->createToken('api-token')->plainTextToken;

    return response()->json([
        'user'  => $user,
        'token' => $token,
    ], 201);
});

// /api/login
Route::post('/login', function (Request $request) {
    $validated = $request->validate([
        'email'    => 'required|email',
        'password' => 'required|string',
    ]);

    $user = User::where('email', $validated['email'])->first();

    if (! $user || ! Hash::check($validated['password'], $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    $token = $user->createToken('api-token')->plainTextToken;

    return response()->json([
        'user'  => $user,
        'token' => $token,
    ]);
});

// ================== PROTECTED ROUTES ==================
Route::middleware('auth:sanctum')->group(function () {

    // cek user login
    Route::get('/me', function (Request $request) {
        return $request->user();
    });

    // ---------- PROJECTS ----------
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::get('/projects/{project}', [ProjectController::class, 'show']);
    Route::put('/projects/{project}', [ProjectController::class, 'update']);
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);

    Route::post('/projects/{project}/members', [ProjectController::class, 'addMember']);
    Route::delete('/projects/{project}/members/{user}', [ProjectController::class, 'removeMember']);

    // ---------- TASKS ----------
    Route::get('/projects/{project}/tasks', [TaskController::class, 'index']);
    Route::post('/projects/{project}/tasks', [TaskController::class, 'store']);

    Route::get('/tasks/{task}', [TaskController::class, 'show']);
    Route::put('/tasks/{task}', [TaskController::class, 'update']);
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);

    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus']);
    Route::patch('/tasks/{task}/assign', [TaskController::class, 'assignUser']);

    Route::post('/tasks/{task}/labels', [TaskController::class, 'attachLabel']);
    Route::delete('/tasks/{task}/labels/{label}', [TaskController::class, 'detachLabel']);

    // ---------- LABELS ----------
    Route::get('/labels', [LabelController::class, 'index']);
    Route::post('/labels', [LabelController::class, 'store']);
    Route::put('/labels/{label}', [LabelController::class, 'update']);
    Route::delete('/labels/{label}', [LabelController::class, 'destroy']);

    // COMMENTS
    Route::get('/tasks/{task}/comments', [CommentController::class, 'index']);
    Route::post('/tasks/{task}/comments', [CommentController::class, 'store']);
    Route::put('/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

    // ATTACHMENTS
    Route::get('/tasks/{task}/attachments', [AttachmentController::class, 'index']);
    Route::post('/tasks/{task}/attachments', [AttachmentController::class, 'store']);
    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy']);

    // TIME TRACKING
    Route::get('/tasks/{task}/time-entries', [TimeEntryController::class, 'index']);
    Route::post('/tasks/{task}/time-entries', [TimeEntryController::class, 'store']);

    Route::post('/tasks/{task}/time-entries/start', [TimeEntryController::class, 'start']);
    Route::post('/tasks/{task}/time-entries/stop', [TimeEntryController::class, 'stop']);

    // summary per project
    Route::get('/projects/{project}/time-report', [TimeEntryController::class, 'projectReport']);

});
