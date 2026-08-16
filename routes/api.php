<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CredenciadosController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ConveniosController;
use App\Http\Controllers\TabelasController;
use App\Http\Controllers\TabelasItensController;
use App\Http\Controllers\UnidadesController;
use App\Http\Controllers\GruposController;
use App\Http\Controllers\ConselhosController;
use App\Http\Controllers\EspecialidadesController;
use App\Http\Controllers\MedicosController;
use App\Http\Controllers\PacientesController;
use App\Http\Controllers\CredenciadosConveniosController;
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

Route::middleware('auth:sanctum')->prefix('tabelas')->group(function() {
    Route::post('/insert', [TabelasController::class, 'insert']);
    Route::get('/index', [TabelasController::class, 'index']);
    Route::get('/{id}', [TabelasController::class, 'show']);
    Route::put('/update/{id}', [TabelasController::class, 'update']);
    Route::delete('/{id}', [TabelasController::class, 'destroy']);
    Route::post('/restore/{id}', [TabelasController::class, 'restore']);
});

Route::middleware('auth:sanctum')->prefix('tabelas-itens')->group(function() {
    Route::post('/insert', [TabelasItensController::class, 'insert']);
    Route::get('/index/{tabelaId}', [TabelasItensController::class, 'index']);
    Route::get('/{id}', [TabelasItensController::class, 'show']);
    Route::put('/update/{id}', [TabelasItensController::class, 'update']);
    Route::delete('/{id}', [TabelasItensController::class, 'destroy']);
    Route::post('/restore/{id}', [TabelasItensController::class, 'restore']);
});

Route::middleware('auth:sanctum')->get('/unidades/index', [UnidadesController::class, 'index']);
Route::middleware('auth:sanctum')->get('/grupos/index', [GruposController::class, 'index']);
Route::middleware('auth:sanctum')->get('/conselhos/index', [ConselhosController::class, 'index']);
Route::middleware('auth:sanctum')->get('/especialidades/index', [EspecialidadesController::class, 'index']);

Route::middleware('auth:sanctum')->prefix('medicos')->group(function () {
    Route::get('/index', [MedicosController::class, 'index']);
    Route::post('/insert', [MedicosController::class, 'insert']);
    Route::put('/update/{id}', [MedicosController::class, 'update']);
    Route::delete('/{id}', [MedicosController::class, 'delete']);
    Route::post('/restore/{id}', [MedicosController::class, 'restore']);
});

Route::middleware('auth:sanctum')->prefix('pacientes')->group(function () {
    Route::get('/index', [PacientesController::class, 'index']);
    Route::post('/insert', [PacientesController::class, 'insert']);
    Route::put('/update/{id}', [PacientesController::class, 'update']);
    Route::delete('/{id}', [PacientesController::class, 'delete']);
    Route::post('/restore/{id}', [PacientesController::class, 'restore']);
});

Route::middleware('auth:sanctum')->prefix('credenciados-convenios')->group(function() {
    Route::post('/create', [CredenciadosConveniosController::class, 'create']);
    Route::get('/index/{credenciadoId}', [CredenciadosConveniosController::class, 'index']);
    Route::get('/{id}', [CredenciadosConveniosController::class, 'show']);
    Route::put('/{id}', [CredenciadosConveniosController::class, 'update']);
    Route::delete('/{id}', [CredenciadosConveniosController::class, 'destroy']);
    Route::post('/restore/{id}', [CredenciadosConveniosController::class, 'restore']);
});

Route::middleware('auth:sanctum')->get('/restoreEstados', [AppUtils::class , 'restoreEstados']);
Route::get('/queryCNPJ/{cnpj}', [AppUtils::class , 'queryCnpj']);
