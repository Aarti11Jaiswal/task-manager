<?php

use Illuminate\Support\Facades\Route;

// Registration 
Route::get('/', function () {
    return view('auth.register'); 
})->name('register');

// Login 
Route::get('/login', function () {
    return view('auth.login'); 
})->name('login');

// Task 
Route::get('/tasks', function () {
    return view('tasks.index'); 
})->name('tasks');
