<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::resource('projects', \App\Http\Controllers\ProjectController::class)->only(['store', 'update', 'destroy']);
    Route::get('/projects/{project}/visualize', [\App\Http\Controllers\ProjectController::class, 'showVisualize'])->name('projects.visualize');
    Route::get('/projects/{project}/activities', [\App\Http\Controllers\ProjectController::class, 'activities'])->name('projects.activities');
    Route::patch('/projects/{project}/members/{user}', [\App\Http\Controllers\ProjectController::class, 'updateMemberRole'])->name('projects.members.update');
    Route::delete('/projects/{project}/members/{user}', [\App\Http\Controllers\ProjectController::class, 'removeMember'])->name('projects.members.remove');
    Route::get('/tasks/{task}', [\App\Http\Controllers\TaskController::class, 'show'])->name('tasks.show');
    Route::post('/projects/{project}/tasks', [\App\Http\Controllers\TaskController::class, 'store'])->name('tasks.store');
    Route::put('/tasks/{task}', [\App\Http\Controllers\TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [\App\Http\Controllers\TaskController::class, 'destroy'])->name('tasks.destroy');
    
    // Comment Routes
    Route::get('/tasks/{task}/comments', [\App\Http\Controllers\CommentController::class, 'index'])->name('comments.index');
    Route::post('/tasks/{task}/comments', [\App\Http\Controllers\CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [\App\Http\Controllers\CommentController::class, 'destroy'])->name('comments.destroy');

    // Attachment Routes
    Route::get('/tasks/{task}/attachments', [\App\Http\Controllers\AttachmentController::class, 'index'])->name('attachments.index');
    Route::post('/tasks/{task}/attachments', [\App\Http\Controllers\AttachmentController::class, 'store'])->name('attachments.store');
    Route::delete('/attachments/{attachment}', [\App\Http\Controllers\AttachmentController::class, 'destroy'])->name('attachments.destroy');
    Route::get('/tasks/{task}/attachments/download', [\App\Http\Controllers\AttachmentController::class, 'download'])->name('attachments.download');

    // Notification Routes
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/settings', [\App\Http\Controllers\NotificationController::class, 'updateSettings'])->name('notifications.updateSettings');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.markAllRead');

    // Report Routes
    Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');

    // Timer Routes
    Route::post('/tasks/{task}/timer/start', [\App\Http\Controllers\TimeEntryController::class, 'start'])->name('tasks.timer.start');
    Route::post('/tasks/{task}/timer/stop', [\App\Http\Controllers\TimeEntryController::class, 'stop'])->name('tasks.timer.stop');
    Route::get('/tasks/{task}/timer', [\App\Http\Controllers\TimeEntryController::class, 'index'])->name('tasks.timer.index');

    // Admin Routes
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/admin/users', [\App\Http\Controllers\UserController::class, 'index'])->name('admin.users.index');
        Route::post('/admin/users', [\App\Http\Controllers\UserController::class, 'store'])->name('admin.users.store');
        Route::put('/admin/users/{user}', [\App\Http\Controllers\UserController::class, 'update'])->name('admin.users.update');
        Route::delete('/admin/users/{user}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('admin.users.destroy');
        // Legacy or specific patch if needed, but update covers it
        Route::patch('/admin/users/{user}/role', [\App\Http\Controllers\UserController::class, 'updateRole'])->name('admin.users.updateRole');
    });
});

require __DIR__.'/auth.php';
