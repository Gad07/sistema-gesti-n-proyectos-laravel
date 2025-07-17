<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\ContentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Dashboard principal
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Rutas de Proyectos
Route::resource('projects', ProjectController::class);
Route::get('projects/{project}/kanban', [ProjectController::class, 'kanban'])->name('projects.kanban');
Route::get('projects/{project}/gantt', [ProjectController::class, 'gantt'])->name('projects.gantt');

// Rutas de Tareas
Route::resource('projects.tasks', TaskController::class)->except(['index', 'show']);
Route::post('tasks/{task}/update-column', [TaskController::class, 'updateColumn'])->name('tasks.update-column');
Route::post('tasks/{task}/update-position', [TaskController::class, 'updatePosition'])->name('tasks.update-position');

// Rutas de Tickets
Route::resource('tickets', TicketController::class);
Route::post('tickets/{ticket}/change-status', [TicketController::class, 'changeStatus'])->name('tickets.change-status');

// Rutas de Media/Multimedia
Route::post('media/upload', [MediaController::class, 'upload'])->name('media.upload');
Route::delete('media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');

// Rutas de Reuniones
Route::resource('meetings', MeetingController::class);

// Rutas de Contenido Público
Route::resource('contents', ContentController::class);
Route::get('public-content', [ContentController::class, 'publicIndex'])->name('contents.public');

// Rutas API para funcionalidades AJAX
Route::prefix('api')->group(function () {
    Route::post('tasks/reorder', [TaskController::class, 'reorder'])->name('api.tasks.reorder');
    Route::get('projects/{project}/tasks-data', [ProjectController::class, 'getTasksData'])->name('api.projects.tasks-data');
});
