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

Route::any('/calculos', 'CalculosController@exibirCalculos')->name('exibir_calculos');

Route::post('salvar_consolidado', 'IndexController@salvarConsolidado')->name('salvar_consolidado');
Route::post('salvar_savings', 'IndexController@salvarSavings')->name('salvar_savings');
Route::post('salvar_movimentacao', 'IndexController@salvarMovimentacao')->name('salvar_movimentacao');
Route::post('atualizar_movimentacao', 'IndexController@AtualizarMovimentacao')->name('atualizar_movimentacao');
Route::post('atualizar_valor_movimentacao', 'IndexController@AtualizarValorMovimentacao')->name('atualizar_valor_movimentacao');
Route::post('atualizar_nome_movimentacao', 'IndexController@AtualizarNomeMovimentacao')->name('atualizar_nome_movimentacao');
Route::post('excluir_movimentacao', 'IndexController@ExcluirMovimentacao')->name('excluir_movimentacao');
Route::post('atualizar_save', 'IndexController@AtualizarSave')->name('atualizar_save');
Route::post('atualizar_posicoes', 'IndexController@AtualizarPosicoes')->name('atualizar_posicoes');
Route::post('nome_movimentacao', 'IndexController@getNomeMovimentacao')->name('nome_movimentacao');
Route::post('definir_itau', 'IndexController@definirItau')->name('definir_itau');
Route::post('definir_mercado_pago', 'IndexController@definirMercadoPago')->name('definir_mercado_pago');
Route::any('exportar', 'ExportarController@exportar')->name('exportar');
Route::get('fechar_mes/{mes}', 'CalculosController@fecharMes')->name('fechar_mes');