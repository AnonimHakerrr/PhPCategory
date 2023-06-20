<?php

use App\Http\Controllers\API\ProductsController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\CategoryController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);
});
Route::get("/category", [CategoryController::class, "index"]);
Route::post("/category", [CategoryController::class,"store"]);
Route::delete('/category/{id}',  [CategoryController::class,"destroy"]);
Route::post("/category/{id}", [CategoryController::class,"update"]);
Route::get("/category/{id}", [CategoryController::class, "edit"]);

Route::get("/products", [ProductsController::class, "index"]);
Route::post("/products", [ProductsController::class,"store"]);
Route::post("/products/{id}", [ProductsController::class,"update"]);
Route::delete("/products/{id}", [ProductsController::class,"destroy"]);
Route::get("/products/{id}", [ProductsController::class, "edit"]);
