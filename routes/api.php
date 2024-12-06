<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\attendanceController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
/**
 * route "/register"
 * @method "POST"
 */
Route::post('/create', [App\Http\Controllers\Api\RegisterController::class, 'register'])->middleware('jwt.verify');
Route::put('/update/{id}', [App\Http\Controllers\Api\RegisterController::class, 'update'])->middleware('jwt.verify');
Route::get('/get', [App\Http\Controllers\Api\RegisterController::class, 'index'])->middleware('jwt.verify');
Route::get('/get/{id}', [App\Http\Controllers\Api\RegisterController::class, 'show'])->middleware('jwt.verify');
Route::delete('/delete/{id}', [App\Http\Controllers\Api\RegisterController::class, 'destroy'])->middleware('jwt.verify');
/**
 * route "/login"
 * @method "POST"
 */
Route::post('/login', App\Http\Controllers\Api\LoginController::class)->name('login');

/**
 * route "/user"
 * @method "GET"
 */
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

use App\http\Controllers\kehadiranController;
use App\Models\attendance;

Route::post('/attendance', [attendanceController::class, 'presensi']);
Route::middleware('auth:api')->get('/attendance/history/{id}', [attendanceController::class, 'show1']);
Route::middleware('auth:api')->get('/attendance/summary/{id}', [attendanceController::class, 'summary']);
Route::middleware('auth:api')->post('/attendance/analysis', [attendanceController::class, 'analysis']);






