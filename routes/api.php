<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CredenciadosController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ConveniosController;
use App\Utils\AppUtils;


Route::post('/login', [AuthController::class , 'login'])->name('login');
Route::post('users/create', [UserController::class, 'create']);

Route::middleware('auth:sanctum')->prefix('users')->group(function() {    
    Route::get('/index', [UserController::class, 'index']);
    Route::get('/{id}', [UserController::class, 'show']);
    Route::put('/{id}', [UserController::class, 'update']);
    Route::delete('/{id}', [UserController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->prefix('credenciados')->group(function() {
    Route::middleware('auth:sanctum')->post('/create',[CredenciadosController::class, 'create']);
    /* Route::middleware('auth:sanctum')->get('/{id}', [CredenciadosController::class, 'restore']); */
    Route::middleware('auth:sanctum')->get('index', [CredenciadosController::class, 'index']);
    Route::middleware('auth:sanctum')->put('/{id}', [CredenciadosController::class, 'update']);
});

Route::middleware('auth:sanctum')->prefix('convenios')->group(function() {
    Route::post('/create', [ConveniosController::class, 'create']);
    Route::get('/index', [ConveniosController::class, 'index']);
    Route::get('/{id}', [ConveniosController::class, 'show']);
    Route::put('/{id}', [ConveniosController::class, 'update']);
    Route::delete('/{id}', [ConveniosController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->get('/restoreEstados', [AppUtils::class , 'restoreEstados']);
Route::get('/queryCNPJ/{cnpj}', [AppUtils::class , 'queryCnpj']);
