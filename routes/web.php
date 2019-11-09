<?php

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

Route::get('/', 'IndexController@exibirContas');

Route::post('salvar_consolidado', 'IndexController@salvarConsolidado')->name('salvar_consolidado');
Route::post('salvar_movimentacao', 'IndexController@salvarMovimentacao')->name('salvar_movimentacao');