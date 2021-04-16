<?php

use App\Http\Controllers\MfoController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::put('/editOwn', [UserController::class, 'editOwn']);
Route::get('/changePassword', [UserController::class, 'changePassword']);
Route::get('/logout',[UserController::class,'logout']);
Route::get('/getProfile',[UserController::class,'getProfile']);
Route::post('/login', [UserController::class, 'login']);
Route::get('/users', [UserController::class, 'index']);

Route::get('/index', [MfoController::class, 'index']);
Route::post('/add',[MfoController::class,'add']);
Route::get('/mfo', [MfoController::class,'mfo']);
Route::put('/edit', [MfoController::class, 'edit']);
Route::put('/archive', [MfoController::class, 'archive']);
