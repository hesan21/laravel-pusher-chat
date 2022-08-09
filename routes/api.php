<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\API\AuthController;
use \App\Http\Controllers\API\ChatsController;
use \App\Http\Controllers\API\UsersController;

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
    Route::post('logout', [AuthController::class, 'logout']);

    Route::post('/forgot', [AuthController::class, 'forgot']);
    Route::post('/reset', [AuthController::class, 'reset']);
});

// Authenticated Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function ()
    {
        return auth()->user();
    })->name('users.index');
});


Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('chat')->group(function () {
        Route::get('/list', [ChatsController::class, 'index'])->name('chats.index');
        Route::get('/show/{chat}', [ChatsController::class, 'show'])->name('chats.show');
        Route::post('/send-message', [ChatsController::class, 'sendMessage'])->name('chats.sendMessage');
        Route::post('/create-group', [ChatsController::class, 'createGroup'])->name('chats.createGroup');
        Route::post('/leave-group', [ChatsController::class, 'leaveGroup'])->name('chats.leaveGroup');
        Route::post('/delete-history/{chat}', [ChatsController::class, 'deleteHistory'])->name('chats.deleteHistory');
        Route::post('/add-member/{chat}', [ChatsController::class, 'addMembers'])->name('chats.addMembers');
    });

    Route::prefix('users')->group(function () {
        Route::get('/list', [UsersController::class, 'index'])->name('users.index');
    });
});
