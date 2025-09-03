<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request){
        $tasks = $request->user()->tasks()->orderBy('due_date')->get();
        return response()->json($tasks);
    }

    public function store(Request $request){
        $request->validate([
            'task_name'=>'required|string|max:255',
            'due_date'=>'required|date|after:now',
            'priority'=>'required|in:low,medium,high',
            'description'=>'nullable|string',
        ]);

        $task = $request->user()->tasks()->create($request->only('task_name','description','due_date','priority'));
        return response()->json($task,201);
    }

    public function update(Request $request, Task $task){
        if($task->user_id !== $request->user()->id){
            return response()->json(['error'=>'Unauthorized'],403);
        }

        $task->update($request->only('task_name','description','due_date','priority'));
        return response()->json($task);
    }

    public function destroy(Request $request, Task $task){
        if($task->user_id !== $request->user()->id){
            return response()->json(['error'=>'Unauthorized'],403);
        }

        $task->delete();
        return response()->json(['message'=>'Task deleted']);
    }

    public function markCompleted(Request $request, Task $task){
        if($task->user_id !== $request->user()->id){
            return response()->json(['error'=>'Unauthorized'],403);
        }

        $task->is_completed = !$task->is_completed;
        $task->save();
        return response()->json($task);
    }
}
