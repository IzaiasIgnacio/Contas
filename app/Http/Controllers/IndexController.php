<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Cartao;
use App\Models\Consolidado;
use App\Models\Movimentacao;
use App\Models\Status;
use App\Models\Tipo;
use Symfony\Component\HttpFoundation\Request;

class IndexController extends Controller {

    public function exibirContas() {
        $cartao = new Cartao();
        $consolidado = new Consolidado();
        $movimentacao = new Movimentacao();
        $movimentacoes_mes = array();
        $maximo_movimentacoes = 0;

        $data = \Carbon\Carbon::createFromFormat('d/m/Y', '01/'.$consolidado->where('nome', 'mes_atual')->first()->valor);

        for ($i=0;$i<=5;$i++) {
            $this->definirValoresFixosMes($data, $movimentacao);
            $movimentacoes_mes[$i] = [
                'mes' => ucfirst($data->locale('pt-br')->monthName),
                'movimentacoes' => $movimentacao->whereMonth('data', $data->format('m'))->where('tipo', '<>', 'save')->get(),
                'save' => $movimentacao->whereMonth('data', $data->format('m'))->where('tipo', 'save')->first()
            ];
            
            if (count($movimentacoes_mes[$i]['movimentacoes']) > $maximo_movimentacoes) {
                $maximo_movimentacoes = count($movimentacoes_mes[$i]['movimentacoes']);
            }
            $data->addMonth();
        }

        
        $total_atual = $consolidado->where('nome', 'itau')->first()->valor + $consolidado->where('nome', 'casa')->first()->valor + $consolidado->where('nome', 'inter')->first()->valor;
        
        return view('index', [
            'helper' => new \App\Models\Helper(),
            'tipos' => Tipo::get(),
            'lista_status' => Status::get(),
            'total_atual' => number_format($total_atual, 2),
            'maximo_movimentacoes' => $maximo_movimentacoes,
            'cartoes' => $cartao->get(),
            'consolidado' => $consolidado,
            'movimentacoes' => $movimentacao->whereRaw("data >= '".$data->format('Y-m-d')."'")->get(),
            'movimentacoes_mes' => $movimentacoes_mes
        ]);
    }

    private function definirValoresFixosMes($data, $movimentacao) {
        $valores_fixos = [
            "virtua" => 200,
            "netflix" => 45.9,
            "m" => 1500,
            "fiesta" => 531.6,
            "vivo" => 49.99
        ];

        foreach ($valores_fixos as $nome => $valor) {
            if ($movimentacao->whereRaw("data = '".$data->format('Y-m-d')."'")->where('nome', $nome)->count() == 0) {
                $mov = new Movimentacao();
                $mov->nome = $nome;
                $mov->valor = $valor;
                $mov->tipo = 'gasto';
                $mov->data = $data->format('Y-m-d');
                $mov->save();
            }
        }
    }

    public function salvarConsolidado(Request $request) {
        $consolidado = Consolidado::where('nome', $request['tipo'])->first();
        $consolidado->valor = str_replace(",", ".", $request['valor']);
        $consolidado->save();
    }
    
    public function salvarMovimentacao(Request $request) {
        $cartao = Cartao::where('nome', $request['cartao'])->first();
        $id_cartao = null;
        if ($cartao != null) {
            $id_cartao = $cartao->id;
        }

        if ($request['parcelas'] == '') {
            $request['parcelas'] = 1;
        }

        $data = date("Y-m-d", strtotime(str_replace('/', '-', $request['data'])));
        $data = date_create($data);

        for ($p=1;$p<=$request['parcelas'];$p++) {
            $movimentacao = new Movimentacao();
            $movimentacao->nome = $request['nome'];
            $movimentacao->data = $data->format('Y-m-d');
            $movimentacao->tipo = $request['tipo'];
            $movimentacao->valor = $request['valor'];
            $movimentacao->status = $request['status'];
            $movimentacao->id_cartao = $id_cartao;
            $movimentacao->save();
        }
    }
}