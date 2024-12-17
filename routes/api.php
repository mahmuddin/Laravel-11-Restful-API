<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\ApiAuthMiddleware;
use Illuminate\Support\Facades\Route;

Route::post('/users', [UserController::class, 'register']);
Route::post('/users/login', [UserController::class, 'login']);

Route::middleware(ApiAuthMiddleware::class)->group(function () {
    Route::get('/users/current', [UserController::class, 'get']);
    Route::patch('/users/current', [UserController::class, 'update']);
    Route::delete('/users/logout', [UserController::class, 'logout']);

    Route::post('/contacts', [ContactController::class, 'create']);
    Route::get('/contacts', [ContactController::class, 'search']);
    Route::get('/contacts/{id}', [ContactController::class, 'get'])->where('id', '[0-9]+');
    Route::put('/contacts/{id}', [ContactController::class, 'update'])->where('id', '[0-9]+');
    Route::delete('/contacts/{id}', [ContactController::class, 'delete'])->where('id', '[0-9]+');

    Route::post('/contacts/{idContact}/address', [AddressController::class, 'create'])->where('idContact', '[0-9]+');
    Route::get('/contacts/{idContact}/address', [AddressController::class, 'list'])->where('idContact', '[0-9]+');
    Route::get('/contacts/{idContact}/address/{idAddress}', [AddressController::class, 'get'])->where('idContact', '[0-9]+')->where('idAddress', '[0-9]+');
    Route::put('/contacts/{idContact}/address/{idAddress}', [AddressController::class, 'update'])->where('idContact', '[0-9]+')->where('idAddress', '[0-9]+');
    Route::delete('/contacts/{idContact}/address/{idAddress}', [AddressController::class, 'delete'])->where('idContact', '[0-9]+')->where('idAddress', '[0-9]+');
});
