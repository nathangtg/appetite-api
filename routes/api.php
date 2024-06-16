<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderedItemsController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ! Index Route
Route::get('/restaurants', [RestaurantController::class, 'index']);
Route::get('/restaurants/{id}', [RestaurantController::class, 'show']);

Route::get('/menus/{restaurant_id}/client', [MenuController::class, 'clientIndex']);
Route::get('/menus/{restaurant_id}', [MenuController::class, 'index']);
Route::get('/menus/{restaurant_id}/{id}', [MenuController::class, 'show']);

Route::prefix('auth')->group(function () {
Route::post('register', [RegisteredUserController::class, 'store']);
Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

// ! Password Reset
Route::post('forgot-password', [PasswordResetLinkController::class, 'store']);
Route::post('reset-password', [NewPasswordController::class, 'store']);

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
// Route::get('restaurants', [RestaurantController::class, 'index']);
Route::post('create/restaurants', [RestaurantController::class, 'store']);
Route::put('update/restaurants/{id}', [RestaurantController::class, 'update']);
Route::delete('delete/restaurants/{id}', [RestaurantController::class, 'destroy']);
Route::post('upload/restaurants/{id}', [RestaurantController::class, 'upload']);

// * Menu CRUD
Route::get('menus/{restaurant_id}', [MenuController::class, 'index']);
// Route::get('menus/{restaurant_id}/{id}', [MenuController::class, 'show']);
Route::post('menus/{restaurant_id}/create', [MenuController::class, 'store']);
Route::put('menus/{restaurant_id}/{id}/update', [MenuController::class, 'update']);
Route::delete('menus/{restaurant_id}/{id}/delete', [MenuController::class, 'destroy']);
Route::post('menus/{restaurant_id}/{id}/upload', [MenuController::class, 'upload']);

// * Order CRUD
Route::get('orders/{restaurant_id}', [OrderController::class, 'index']);
Route::get('orders/{restaurant_id}/{id}', [OrderController::class, 'getOrderAndItems']);
Route::get('orders', [OrderController::class, 'userOrders']);
Route::post('orders/{restaurant_id}/create', [OrderController::class, 'store']);
Route::put('orders/{restaurant_id}/{id}/update', [OrderController::class, 'update']);
Route::delete('orders/{restaurant_id}/{id}/delete', [OrderController::class, 'destroy']);

// * Order Items CRUD
Route::get('order-items', [OrderedItemsController::class, 'index']);
Route::get('order-items/{restaurant_id}', [OrderedItemsController::class, 'showByRestaurant']);
Route::post('order-items/{restaurant_id}/create', [OrderedItemsController::class, 'store']);
Route::put('order-items/{restaurant_id}/{order_id}/{id}/update', [OrderedItemsController::class, 'update']);
Route::delete('order-items/{restaurant_id}/{order_id}/{id}/delete', [OrderedItemsController::class, 'destroy']);

// ! Admin Indexes
// * Restaurant
Route::get('admin/restaurants', [RestaurantController::class, 'adminIndex']);
Route::get('admin/restaurants/{restaurant_id}', [RestaurantController::class, 'adminIndexID']);
});
