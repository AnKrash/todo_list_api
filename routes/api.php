<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SubtaskController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/login', [AuthController::class,'login']);

Route::group(['prefix' => 'tasks'], function () {
    Route::get('/index', [TaskController::class, 'index'])->name('index.show');
    Route::get('/show/{id}', [TaskController::class, 'show'])->name('task.show');
    Route::get('/edit/{id}', [TaskController::class, 'edit'])->name('task.edit');
    Route::put('/update/{id}', [TaskController::class, 'update'])->name('task.update')->middleware('auth:sanctum');
    Route::delete('/delete/{id}', [TaskController::class, 'destroy'])->name('task.destroy')->middleware('auth:sanctum');
    Route::post('/store', [TaskController::class, 'store'])->name('task.store');
    Route::put('/mark-as-done/{id}', [TaskController::class, 'markAsDone'])->name('markAsDone');
});

