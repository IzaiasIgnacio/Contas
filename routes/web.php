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
Route::post('atualizar_movimentacao', 'IndexController@AtualizarMovimentacao')->name('atualizar_movimentacao');
Route::post('excluir_movimentacao', 'IndexController@ExcluirMovimentacao')->name('excluir_movimentacao');
Route::post('atualizar_save', 'IndexController@AtualizarSave')->name('atualizar_save');
Route::post('atualizar_posicoes', 'IndexController@AtualizarPosicoes')->name('atualizar_posicoes');