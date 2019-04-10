<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Cartao;
use App\Models\Consolidado;
use App\Models\Movimentacao;

class IndexController extends Controller {

    public function exibirContas() {
        $cartao = new Cartao();
        $consolidado = new Consolidado();
        $movimentacao = new Movimentacao();
        $movimentacoes_mes = array();
        $maximo_movimentacoes = 0;

        $data = \Carbon\Carbon::createFromFormat('d/m/Y', '01/'.$consolidado->where('nome', 'mes_atual')->first()->valor);

        for ($i=0;$i<=5;$i++) {
            $movimentacoes_mes[$i] = [
                'mes' => ucfirst($data->locale('pt-br')->monthName),
                'movimentacoes' => $movimentacao->whereMonth('data', $data->format('m'))->get()
            ];
            
            if (count($movimentacoes_mes[$i]['movimentacoes']) > $maximo_movimentacoes) {
                $maximo_movimentacoes = count($movimentacoes_mes[$i]['movimentacoes']);
            }
            $data->addMonth();
        }
        
        return view('index', [
            'maximo_movimentacoes' => $maximo_movimentacoes,
            'cartoes' => $cartao->get(),
            'consolidado' => $consolidado,
            'movimentacoes' => $movimentacao->whereRaw('data >= '.$data->format('Y-m-d'))->get(),
            'movimentacoes_mes' => $movimentacoes_mes
        ]);
    }
    
}