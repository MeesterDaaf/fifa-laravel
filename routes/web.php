<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeelnemersController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RanglijstController;
use App\Http\Controllers\ToernooiController;
use App\Http\Controllers\VoorspellingenController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function () {
    Route::get('/', [HomeController::class, 'index']);
    Route::get('/ranglijst', [RanglijstController::class, 'index']);
    Route::get('/voorspellingen', [VoorspellingenController::class, 'index']);
    Route::get('/voorspellingen/{id}', [VoorspellingenController::class, 'show']);
    Route::post('/voorspellingen/{id}', [VoorspellingenController::class, 'store']);
    Route::get('/toernooi', [ToernooiController::class, 'index']);
    Route::post('/toernooi', [ToernooiController::class, 'store']);
    Route::get('/deelnemers', [DeelnemersController::class, 'index']);
    Route::get('/deelnemers/{user}', [DeelnemersController::class, 'show']);
    Route::get('/profiel', [ProfileController::class, 'edit']);
    Route::put('/profiel', [ProfileController::class, 'update']);
    Route::delete('/profiel', [ProfileController::class, 'destroy']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/', [AdminController::class, 'index']);
        Route::post('/sync', [AdminController::class, 'syncMatches']);
        Route::post('/sync-squads', [AdminController::class, 'syncSquads']);
        Route::post('/match/{id}', [AdminController::class, 'updateMatch']);
        Route::post('/tournament', [AdminController::class, 'updateTournament']);
        Route::post('/invite/regenerate', [AdminController::class, 'regenerateInviteCode']);
        Route::post('/users/{user}/toggle-admin', [AdminController::class, 'toggleAdmin']);
        Route::delete('/users/{user}', [AdminController::class, 'deleteUser']);
    });
});
