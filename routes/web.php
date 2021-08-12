<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/install', [App\Http\Controllers\ShopifyController::class, 'install'])->name('install');
Route::get('/authenticate', [App\Http\Controllers\ShopifyController::class, 'authenticate'])->name('authenticate');

Route::group(['prefix'=>'/admin','as'=>'admin.'], function(){
    Route::get('/products', [App\Http\Controllers\ProductController::class, 'getProducts'])->name('install');
    Route::get('/products/listed', [App\Http\Controllers\ProductController::class, 'listedProducts'])->name('listed');
    Route::get('/products/imported', [App\Http\Controllers\ProductController::class, 'importedProducts'])->name('imported');
});