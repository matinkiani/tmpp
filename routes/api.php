<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatRoomController;
use App\Http\Controllers\Api\MessageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Chat room routes
    Route::apiResource('chat-rooms', ChatRoomController::class);
    Route::post('/chat-rooms/{chatRoom}/join', [ChatRoomController::class, 'join']);
    Route::post('/chat-rooms/{chatRoom}/leave', [ChatRoomController::class, 'leave']);

    // Message routes
    Route::get('/chat-rooms/{chatRoom}/messages', [MessageController::class, 'index']);
    Route::post('/chat-rooms/{chatRoom}/messages', [MessageController::class, 'store']);
    Route::get('/chat-rooms/{chatRoom}/messages/{message}', [MessageController::class, 'show']);
    Route::post('/chat-rooms/{chatRoom}/messages/mark-read', [MessageController::class, 'markAsRead']);

    // User info route
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
