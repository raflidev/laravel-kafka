<?php

use App\Http\Controllers\Api\MessageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('messages', MessageController::class);
Route::post('messages/bulk', [MessageController::class, 'bulkStore']);
Route::post('messages/{message}/retry', [MessageController::class, 'retry']);