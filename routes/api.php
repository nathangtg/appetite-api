<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\UserController;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [RegisteredUserController::class, 'store']);
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum'])->group(function () {
    // ! Deauthentication
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy']);

    // * User OWN CRUD
    Route::get('users', [UserController::class, 'index']);
    Route::get('users/{id}', [UserController::class, 'show']);
    Route::put('update/users/{id}', [UserController::class, 'update']);
    Route::delete('delete/users/{id}', [UserController::class, 'destroy']);

    // * Restaurant CRUD
    Route::get('restaurants', [RestaurantController::class, 'index']);
    Route::get('restaurants/{id}', [RestaurantController::class, 'show']);
    Route::post('create/restaurants', [RestaurantController::class, 'store']);
    Route::put('update/restaurants/{id}', [RestaurantController::class, 'update']);
    Route::delete('delete/restaurants/{id}', [RestaurantController::class, 'destroy']);

});
