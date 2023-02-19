<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PHPMailerController;
use App\Http\Controllers\FileController;

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

/*Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});*/

Route::post('/user', [UserController::class, 'create']);
Route::delete('/user/{id}', [UserController::class, 'delete']);
Route::put('/user', [UserController::class, 'update']);
Route::get('/user', [UserController::class, 'listUsers']);
Route::get('/user/{id}', [UserController::class, 'listUser']);
Route::get('/user/search/{email}', [UserController::class, 'searchUser']);

Route::get('/login', [UserController::class, 'login'])->name('login');
Route::get('/logout', [UserController::class, 'logout'])->name('logout');
Route::get('/reset-password', [UserController::class, 'reset_password']);

Route::group([
    'prefix' => 'admin'
], function () {
    Route::get('user/{id}', [AdminController::class, 'listUser']);
    Route::get('user', [AdminController::class, 'listUsers']);
    Route::delete('user/{id}', [AdminController::class, 'delete']);
    Route::put('user', [AdminController::class, 'update']);
});

Route::post('/directory', [FileController::class, 'makeDirectory']);
Route::put('/directory', [FileController::class, 'renameDirectory']);
Route::get('/directory/{id}', [FileController::class, 'getDirectoryInfo']);
Route::delete('/directory/{id}', [FileController::class, 'deleteDirectory']);

Route::get('/file', [FileController::class, 'getFiles']);
Route::get('/file/{id}', [FileController::class, 'getFileInfo']);
Route::post('/file', [FileController::class, 'uploadFile']);
Route::put('/file', [FileController::class, 'renameOrMoveFile']);
Route::delete('/file/{id}', [FileController::class, 'deleteFile']);

Route::put('/files/share/{id}/{user_id}', [FileController::class, 'addFileAccess']);
Route::delete('/files/share/{id}/{user_id}', [FileController::class, 'deleteUserAccess']);
Route::get('/files/share/{id}', [FileController::class, 'getUsersAccess']);
