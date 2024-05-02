<?php

use App\Http\Auth\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Ruta para la autenticación de usuarios
Route::post('auth/login', [AuthController::class, 'login']);

// Ruta para la recuperación de contraseña
Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword']);

// Rutas protegidas por autenticación con Sanctum
Route::middleware('auth:sanctum')->group( function () {

    // obtener el usuario autenticado actualmente
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // cerrar sesion
    Route::post('auth/logout', [AuthController::class, 'logout']);
    // registrar un usuario nuevo
    Route::post('auth/register', [AuthController::class, 'createUser']);

    // Rutas para los recursos de la API
    Route::apiResources([
        'users' => UserController::class,
    ]);
}); 
