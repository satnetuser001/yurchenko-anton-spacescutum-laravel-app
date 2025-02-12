<?php

use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::apiResource('comments', CommentController::class);
Route::apiResource('orders', OrderController::class)->only(['index', 'store', 'show']);
Route::apiResource('products', ProductController::class);
Route::apiResource('users', UserController::class);