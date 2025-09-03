<?php 
use App\Http\Controllers\TaskController;

Route::get('/tasks', [TaskController::class, 'index']);            // List all tasks
Route::post('/tasks', [TaskController::class, 'store']);           // Create a task
Route::put('/tasks/{task}', [TaskController::class, 'update']);    // Update task
Route::delete('/tasks/{task}', [TaskController::class, 'destroy']); // Delete task
Route::patch('/tasks/{task}/toggle', [TaskController::class, 'markCompleted']); // Toggle completed
