<?php
namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TaskController extends Controller
{
    // List tasks
    public function index()
    {
        $tasks = Task::orderBy('due_date')->get();
        return response()->json($tasks);
    }

    // Create a task
    public function store(Request $request)
    {
        $request->validate([
            'task_name' => 'required|string|max:255',
            'due_date' => 'required|date|after:now',
            'priority' => 'required|in:low,medium,high'
        ]);

        $task = Task::create($request->all());
        return response()->json($task);
    }

    // Update a task
    public function update(Request $request, Task $task)
    {
        $task->update($request->all());
        return response()->json($task);
    }

    // Delete a task
    public function destroy(Task $task)
    {
        $task->delete();
        return response()->json(['message' => 'Task deleted']);
    }

    // Toggle completed
    public function markCompleted(Task $task)
    {
        $task->is_completed = !$task->is_completed;
        $task->save();
        return response()->json($task);
    }
}
