<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Cartao;
use App\Models\Consolidado;
use App\Models\Helper;
use App\Models\Movimentacao;
use Illuminate\Support\Facades\DB;

class ContasController extends Controller {

    private function atualizarBanco() {
        if (file_exists('dump.sql')) {
            DB::unprepared(file_get_contents('dump.sql'));
            // exec("mysql --verbose -u ".env('DB_USERNAME')." -p ".env('DB_PASSWORD')." -h ".env('DB_HOST')." ".env('DB_DATABASE')." < dump.sql 2>&1", $output);
            @unlink('dump.sql');
        }
    }

    public function exibirDashboard() {
        $this->atualizarBanco();

        $consolidado = new Consolidado();
        $movimentacao = new Movimentacao();
        $helper = new Helper();
        $meses = [];

        date_default_timezone_set('America/Sao_Paulo');
        $total_atual = Consolidado::where('totais', 1)->sum('valor');
        $data = \Carbon\Carbon::createFromFormat('d/m/Y', '01/'.$consolidado->where('nome', 'mes_atual')->first()->valor);
        $dia = date('d');
        
        if (date('m') != $data->month) {
            $dia = 0;
        }

        for ($i=0;$i<=11;$i++) {
            $renda = $movimentacao->whereMonth('data', $data->format('m'))->whereYear('data', $data->format('Y'))->where('tipo', 'renda')->where('status', '<>', 'pago')->sum('valor');
            if ($renda == 0) {
                $renda = $consolidado->where('nome', 'salario')->first()->valor;
            }
            $gastos = $movimentacao->whereMonth('data', $data->format('m'))->whereYear('data', $data->format('Y'))->where('tipo', 'gasto')->where('status', '<>', 'pago')->sum('valor');
            $saldo = $total_atual - $gastos + $renda;
            $diferenca = $saldo - $total_atual;
            
            $objetivo = (1000/30)*(30-$dia);
            if ($i == 0) {
                $margem = $saldo-10000;
            }
            else {
                $margem = $saldo-(10000+$i*1000)+1000-$objetivo;
            }

            $meses[$i] = [
                'nome' => ucfirst($data->locale('pt-br')->monthName),
                'total_atual' => $helper->format($total_atual),
                'gastos' => $helper->format($gastos),
                'renda' => $helper->format($renda),
                'saldo' => $helper->format($saldo),
                'diferenca' => $helper->format($diferenca),
                'margem' => $helper->format($margem)
            ];

            $total_atual = $saldo;
            $data->addMonth();
        }

        $cartoes = [];
        $cards = Cartao::where('id', '<>', 4)->where('ativo', 1)->get();

        foreach ($cards as $i => $cartao) {
            $gastos_cartao = Movimentacao::where('id_cartao', $cartao->id)->whereIn('status', ['planejado', 'definido'])->whereIn('tipo', ['gasto', 'terceiros'])->sum('valor');
            $renda_cartao = Movimentacao::where('id_cartao', $cartao->id)->where('status', 'definido')->where('tipo', 'renda')->sum('valor');
            $limite = $cartao->credito-$gastos_cartao+$renda_cartao;
            $fechamento = $helper->data_fechamento($cartao->vencimento, $cartao->dias_fechamento);
            $vencimento = $cartao->vencimento.'/'.date('m', strtotime('first day of +1 month'));

            $data = \Carbon\Carbon::createFromFormat('d/m/Y', '01/'.$consolidado->where('nome', 'mes_atual')->first()->valor);
            // $data->addMonth();
            $meses_cartao = [];

            $limite_atual = $limite;
            for ($m=0;$m<=2;$m++) {
                $gastos = Movimentacao
                                    ::where('id_cartao', $cartao->id)
                                    ->whereIn('status', ['planejado', 'definido'])
                                    ->whereIn('tipo', ['gasto', 'terceiros'])
                                    ->whereMonth('data', $data->format('m'))
                                    ->whereYear('data', $data->format('Y'))
                                        ->sum('valor');
                $limite_atual += $gastos;
                $meses_cartao[$m] = [
                    'nome' => ucfirst($data->locale('pt-br')->monthName),
                    'gastos' => $gastos,
                    'limite_atual' => $limite_atual
                ];
                $data->addMonth();
            }

            $cartoes[$i] = [
                'cartao' => $cartao,
                'limite' => 'R$ '.$helper->format($limite).' / '.$helper->format($cartao->credito),
                'fechamento' => $fechamento,
                'vencimento' => $vencimento,
                'meses_cartao' => $meses_cartao
            ];
        }
        
        return view('layout', [
            'pagina' => 'layout_dashboard',
            'meses' => $meses,
            'cartoes' => $cartoes,
        ]);
    }

    public function exibirContas() {
        $consolidado = new Consolidado();
        $helper = new Helper();

        date_default_timezone_set('America/Sao_Paulo');
        $data = \Carbon\Carbon::createFromFormat('d/m/Y', '01/'.$consolidado->where('nome', 'mes_atual')->first()->valor);

        $meses = [];
        $maximo_movimentacoes = 0;

        for ($i=0;$i<=8;$i++) {
            if ($i == 0) {
                $saldo_inicial = $consolidado::where('atual', 1)->sum('valor');
            }
            
            $meses[$i] = $this->calcular_mes($saldo_inicial, $data, $i, $helper);
            $saldo_inicial = $meses[$i]['saldo'];

            if (count($meses[$i]['movimentacoes']) > $maximo_movimentacoes) {
                $maximo_movimentacoes = count($meses[$i]['movimentacoes']);
            }

            $data->addMonth();
        }

        return view('layout', [
            'helper' => new \App\Models\Helper(),
            'meses' => $meses,
            'maximo_movimentacoes' => $maximo_movimentacoes,
            'pagina' => 'layout_contas'
        ]);
    }

    private function calcular_mes($saldo_inicial, $data, $posicao, $helper) {
        $movimentacao = new Movimentacao();

        $dia = date('d');
        $movimentacoes = Movimentacao
                            ::whereMonth('data', $data->format('m'))
                            ->whereYear('data', $data->format('Y'))
                            ->whereNotIn('tipo', ['save', 'terceiros'])
                            ->where('nome', '!=', 'salario')
                                ->orderBy('tipo')
                                ->orderBy('posicao')
                                    ->get();

        $renda = $movimentacao->whereMonth('data', $data->format('m'))->whereYear('data', $data->format('Y'))->where('tipo', 'renda')->where('status', '<>', 'pago')->sum('valor');
        $gastos = $movimentacao->whereMonth('data', $data->format('m'))->whereYear('data', $data->format('Y'))->where('tipo', 'gasto')->where('status', '<>', 'pago')->sum('valor');
        
        $saldo = $saldo_inicial - $gastos + $renda;
        $saldo_total = $helper->getTotalSavingsAtual() + $saldo;
        
        if ($posicao == 0) {
            $margem = $saldo_total - 4000;
        }
        else {
            $margem = $saldo_total-(4000+$posicao*1000)+1000-((1000/30)*(30-$dia));
        }
        
        return [
                'nome' => ucfirst($data->locale('pt-br')->monthName),
                'movimentacoes' => $movimentacoes,
                'total' => $gastos - $renda, // gastos a pagar - renda a receber
                'gastos' => $gastos,
                'renda' => $renda,
                'saldo' => $saldo, // saldo inicial - gastos + renda (save)
                'saldo_total' => $saldo_total, // savings + saldo
                'margem' => $margem
            ];
    }
}