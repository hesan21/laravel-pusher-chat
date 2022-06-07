<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\API\AuthController;
use \App\Http\Controllers\API\ChatsController;

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
// Auth Routes
Route::middleware('guest')->prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::post('login', [AuthController::class, 'login'])->name('login');
});

// Authenticated Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('chat')->group(function () {
        Route::get('/', [ ChatsController::class, 'index' ])->name('chats.index');
        Route::get('/show/{chat}', [ ChatsController::class, 'show' ])->name('chats.show');
        Route::post('/send-message', [ ChatsController::class, 'sendMessage'])->name('chats.sendMessage');
        Route::post('/create-group', [ ChatsController::class, 'createGroup'])->name('chats.createGroup');
        Route::post('/leave-group', [ ChatsController::class, 'leaveGroup'])->name('chats.leaveGroup');
    });

    Route::post('auth/logout', [AuthController::class, 'logout']);
});
