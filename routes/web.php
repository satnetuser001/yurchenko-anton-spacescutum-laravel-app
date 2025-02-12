<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\UserController;

Route::get('/', [UserController::class, 'home'])->name('home');

Route::post('/login', [UserController::class, 'login'])->name('login');
Route::post('/logout', [UserController::class, 'logout'])->name('logout');

Route::get('/register', [UserController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [UserController::class, 'register'])->name('register');

Route::get('/profile', [UserController::class, 'profile'])->name('profile');
Route::post('/regenerate-token', [UserController::class, 'regenerateToken'])->name('regenerate-token');
