<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ClientController;
use App\Http\Controllers\API\TrainerController;
use App\Http\Controllers\API\ExerciseController;
use App\Http\Controllers\API\WorkoutController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\MessageController;

// Get CSRF cookie (per frontend SPA)
Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);

// Public: Registrazione trainer
Route::post('/register/trainer', [TrainerController::class, 'register']);

// Accesso per tutti gli utenti autenticati
Route::middleware(['auth:sanctum'])->group(function () {
    
    // User info
    Route::prefix('user')->group(function () {
        Route::get('/', [UserController::class, 'show']);
    });

    // Exercises - visibili da tutti
    Route::prefix('exercises')->controller(ExerciseController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('{id}', 'show');
    });

    // Workouts - visibili da trainer e client
    Route::prefix('workouts')->controller(WorkoutController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('{id}', 'show');
    });

    // Notifications visibili all'utente autenticato
    Route::prefix('notifications')->controller(NotificationController::class)->group(function () {
        Route::get('/', 'index');
        Route::patch('{id}/read', 'markAsRead');
    });

    // Messaggi: invio e lettura
    Route::prefix('messages')->controller(MessageController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::post('{id}/read', 'markAsRead');
    });

});

// Solo per Trainer
Route::middleware(['auth:sanctum', 'trainer'])->group(function () {

    // Clients
    Route::prefix('clients')->controller(ClientController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('{id}', 'show');
        Route::put('{id}', 'update');
        Route::delete('{id}', 'destroy');
    });

    // Exercises
    Route::prefix('exercises')->controller(ExerciseController::class)->group(function () {
        Route::post('/', 'store');
        Route::put('{id}', 'update');
        Route::delete('{id}', 'destroy');
    });

    // Workouts
    Route::prefix('workouts')->controller(WorkoutController::class)->group(function () {
        Route::post('/', 'store');
        Route::put('{id}', 'update');
        Route::delete('{id}', 'destroy');
    });

    //Notifiche
    Route::prefix('notifications')->controller(NotificationController::class)->group(function () {
        Route::post('/', 'store');
        Route::delete('{id}', 'destroy');
    });
});
