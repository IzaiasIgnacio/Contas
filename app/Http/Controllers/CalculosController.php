<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Consolidado;
use App\Models\Movimentacao;
use App\Models\Responsavel;
use Symfony\Component\HttpFoundation\Request;

class CalculosController extends Controller {

    private function atualizarBanco() {
        if (file_exists('dump.sql')) {
            exec("mysql -u3280436_contas -pa9TUW813KliNIe -hfdb24.awardspace.net 3280436_contas < dump.sql");
            @unlink('dump.sql');
        }
    }

    public function exibirCalculos(Request $request) {
        if (empty($request['mes']) || empty($request['ano'])) {
            return view('selecionar_data_calculos');
        }
        
        $this->atualizarBanco();
        
        $data = $request['mes'].'.'.$request['ano'];
        $d = explode(".", $data);
        $data = \Carbon\Carbon::createFromFormat('d/m/Y', '01/'.$d[0]."/".$d[1]);

        $helper = new \App\Models\Helper();
        $movimentacao = new Movimentacao();
        $responsaveis = Responsavel::get();
        $responsaveis['izaias'] = 'Izaias';
        $gastos = [];
        $total = 0;
        $total_com_atrasado = 0;
        $total_chah = 0;
        $total_cristiane = 0;

        $movimentacoes = $movimentacao->whereMonth('data', $data->format('m'))
                                       ->whereYear('data', $data->format('Y'))
                                       ->where('tipo', 'terceiros')
                                        ->get();
        
        $izaias = $movimentacao->whereMonth('data', $data->format('m'))
                                        ->whereYear('data', $data->format('Y'))
                                        ->where('tipo', 'gasto')
                                        ->where('id_cartao', 4)
                                         ->get();

        $itau = $movimentacao->whereMonth('data', $data->format('m'))
                                         ->whereYear('data', $data->format('Y'))
                                         ->whereIn('tipo', ['gasto', 'renda'])
                                         ->where('itau', true)
                                          ->get();
        $total_itau = 0;
        // $saque = new Movimentacao();
        // $saque->nome = 'saque';
        // $saque->valor = 450;
        // $saque->tipo = 'gasto';
        
        $deposito = new Movimentacao();
        $deposito->nome = 'entrada';
        $deposito->valor = Consolidado::where('nome', 'entrada')->first()->valor;
        $deposito->tipo = 'renda';
        
        $itau[] = $deposito;
        // $itau[] = $saque;
        foreach ($itau as $it) {
            if ($it->tipo == 'gasto') {
                $total_itau += $it->valor;
            }
            if ($it->tipo == 'renda') {
                $total_itau -= $it->valor;
            }
        }
        $valor_itau = Consolidado::where('nome', 'itau')->first()->valor;

        foreach ($movimentacoes as $movimentacao) {
            $gastos[$movimentacao->responsavel][] = $movimentacao;
            switch ($movimentacao->responsavel) {
                case 'chah':
                    $total_chah += $movimentacao->valor;
                break;
                case 'cristiane':
                    $total_cristiane += $movimentacao->valor;
                break;
                default:
                    $total += $movimentacao->valor;
                break;
            }
        }

        $antigo_chah = new Movimentacao();
        $antigo_chah->nome = 'Antigo';
        $antigo_chah->valor = 238.84;
        $gastos['chah'][] = $antigo_chah;

        $mes = new Movimentacao();
        $mes->nome = 'mês';
        $mes->valor = 1300;
        $gastos['izaias'][] = $mes;
        $total -= $mes->valor;

        $pago = 0;
        if ($d[0] == date('m') && $d[1] == date('Y')) {
            $pago = Consolidado::where('nome', 'pago')->first()->valor;
        }
        
        foreach ($izaias as $i) {
            $gastos['izaias'][] = $i;
            $total -= $i->valor;
        }
        
        return view('calculos', [
            'helper' => $helper,
            'responsaveis' => $responsaveis,
            'gastos' => $gastos,
            'pago' => $pago,
            'total' => $total,
            'total_chah' => $total_chah,
            'total_cristiane' => $total_cristiane,
            'itau' => $itau,
            'total_itau' => $total_itau,
            'valor_itau' => $valor_itau
        ]);
    }

}