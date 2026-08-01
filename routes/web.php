<?php

use Illuminate\Support\Facades\Route;
use App\Utils\AppUtils;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/restoreEstados', [AppUtils::class , 'restoreEstados']);
Route::get('/queryCNPJ/{cnpj}', [AppUtils::class , 'queryCnpj']);
