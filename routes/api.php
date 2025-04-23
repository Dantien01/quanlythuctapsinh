<?php

use App\Http\Controllers\InternController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Route công khai (không cần token)
Route::post('login', [AuthController::class, 'login']);

// Các route yêu cầu token
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('interns', InternController::class);
    Route::apiResource('projects', ProjectController::class);
    Route::apiResource('tasks', TaskController::class);
    Route::apiResource('evaluations', EvaluationController::class);
    Route::apiResource('schedules', ScheduleController::class);
    Route::post('logout', [AuthController::class, 'logout']);
});