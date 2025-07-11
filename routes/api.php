<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/catalog/products', [ProductController::class, 'products']);
Route::get('/catalog/filters', [ProductController::class, 'filters']);
