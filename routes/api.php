<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlayersController;
use App\Http\Controllers\ItemsController;

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

Route::get('/players', [PlayersController::class, 'index']);
Route::get('/players/{id}', [PlayersController::class, 'show']);
Route::post('/players', [PlayersController::class, 'create']);
Route::put('/players/{id}', [PlayersController::class, 'update']);
Route::delete('/players/{id}', [PlayersController::class, 'destroy']);
Route::post('/players/{id}/addItem', [PlayersController::class, 'addItem']);
Route::post('/players/{id}/useItem', [PlayersController::class, 'useItem']);
Route::post('/players/{id}/useGacha',[PlayersController::class,'useGacha']);


Route::post('/items', [ItemsController::class, 'store']); // 挿入
Route::put('/items/{id}', [ItemsController::class, 'update']); // 更新
Route::delete('items/{id}', [ItemsController::class, 'destroy']); // 削除
