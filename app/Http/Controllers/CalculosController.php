<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Consolidado;
use App\Models\Movimentacao;
use App\Models\Responsavel;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Support\Facades\DB;

class CalculosController extends Controller {

    private function atualizarBanco() {
        //awardspace
        if (file_exists('dump.sql')) {
            DB::unprepared(file_get_contents('dump.sql'));
            // exec("mysql -u".env('DB_USERNAME')." -p".env('DB_PASSWORD')." -h".env('DB_HOST')." ".env('DB_DATABASE')." < dump.sql");
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
        $total_chah = 0;
        $total_cristiane = 0;
        $total_tio_anisio = 0;

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
        
        $nb = $movimentacao->whereMonth('data', $data->format('m'))
                                         ->whereYear('data', $data->format('Y'))
                                         ->whereIn('tipo', ['gasto', 'renda'])
                                         ->where('nb', true)
                                          ->get();

        $iti = $movimentacao->whereMonth('data', $data->format('m'))
                                         ->whereYear('data', $data->format('Y'))
                                         ->whereIn('tipo', ['gasto', 'renda'])
                                         ->where('iti', true)
                                          ->get();
        $total_itau = 0;
        $total_nb = 0;
        $total_iti = 0;
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

        foreach ($nb as $n) {
            if ($n->tipo == 'gasto') {
                $total_nb += $n->valor;
            }
            if ($n->tipo == 'renda') {
                $total_nb -= $n->valor;
            }
        }
        $valor_nb = Consolidado::where('nome', 'nubank')->first()->valor;

        foreach ($iti as $i) {
            if ($i->tipo == 'gasto') {
                $total_iti += $i->valor;
            }
            if ($i->tipo == 'renda') {
                $total_iti -= $i->valor;
            }
        }
        $valor_iti = Consolidado::where('nome', 'iti')->first()->valor;

        foreach ($movimentacoes as $movimentacao) {
            $gastos[$movimentacao->responsavel][] = $movimentacao;
            switch ($movimentacao->responsavel) {
                case 'chah':
                    $total_chah += $movimentacao->valor;
                break;
                case 'cristiane':
                    $total_cristiane += $movimentacao->valor;
                break;
                case 'tio_anisio':
                    $total_tio_anisio += $movimentacao->valor;
                break;
                default:
                    $total += $movimentacao->valor;
                break;
            }
        }

        $antigo_chah = new Movimentacao();
        $antigo_chah->nome = 'Antigo';
        $antigo_chah->valor = Consolidado::where('nome', 'chah')->first()->valor;
        $gastos['chah'][] = $antigo_chah;

        $mes = new Movimentacao();
        $mes->nome = 'mês';
        $mes->valor = @Movimentacao::where('nome', 'm')->where('data', 'like', $request['ano'].'-'.sprintf("%02d", $request['mes']).'%')->first()->valor;
        $gastos['izaias'][] = $mes;
        $total -= $mes->valor;

        $pago = 0;
        $data_pago = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', Consolidado::where('nome', 'pago')->first()->data_atualizacao);
        $mes_atual = \Carbon\Carbon::createFromFormat('m/Y', Consolidado::where('nome', 'mes_atual')->first()->valor);
        if ($d[0] == date('m') && $d[1] == date('Y') && $data_pago->month == $mes_atual->month) {
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
            'antigo_chah' => $antigo_chah->valor,
            'total_cristiane' => $total_cristiane,
            'total_tio_anisio' => $total_tio_anisio,
            'itau' => $itau,
            'total_itau' => $total_itau,
            'valor_itau' => $valor_itau,
            'nb' => $nb,
            'total_nb' => $total_nb,
            'valor_nb' => $valor_nb,
            'iti' => $iti,
            'total_iti' => $total_iti,
            'valor_iti' => $valor_iti
        ]);
    }

    public function fecharMes($args) {
        $movimentacoes = Movimentacao::whereNotNull('id_cartao')->where('data', 'like', $args.'%');
        foreach ($movimentacoes->get() as $movimentacao) {
            $movimentacao->status = 'pago';
            $movimentacao->save();
        }

        $cartoes = $movimentacoes->select('cartao.nome', 'cartao.vencimento', 'cartao.id', DB::raw('sum(movimentacao.valor) as valor'))->join('cartao', 'cartao.id', 'id_cartao')->where('tipo', '<>', 'renda')->groupBy('cartao.id')->get();
        foreach ($cartoes as $cartao) {
            $mov = new Movimentacao();
            $mov->nome = $cartao->nome.' ('.$cartao->vencimento.')';
            $mov->data = $args.'-01';
            $mov->tipo = 'gasto';
            $mov->valor = $cartao->valor;
            $mov->status = 'planejado';
            $mov->id_cartao = $cartao->id;
            $mov->posicao = 999;
            $mov->save();
        }

        $mae = Movimentacao::where('responsavel', 'mae')->whereIn('nome', ['luz','vivo','nubank'])->where('data', 'like', $args.'%')->get();
        foreach ($mae as $m) {
            $mov = new Movimentacao();
            $mov->nome = $m->nome.' (m)';
            $mov->data = $args.'-01';
            $mov->tipo = 'gasto';
            $mov->valor = $m->valor;
            $mov->status = 'planejado';
            $mov->posicao = 999;
            $mov->save();
        }

        $mae = Movimentacao::where('responsavel', 'mae')->where('nome', 'like', 'pulse%')->where('data', 'like', $args.'%')->get();
        foreach ($mae as $m) {
            $mov = new Movimentacao();
            $mov->nome = $m->nome.' (m)';
            $mov->data = $args.'-01';
            $mov->tipo = 'gasto';
            $mov->valor = $m->valor;
            $mov->status = 'planejado';
            $mov->posicao = 999;
            $mov->save();
        }

        Movimentacao::where('responsavel', 'mae')->where('data', 'like', $args.'%')->update(['status' => 'pago']);
        Movimentacao::where('nome', 'm')->where('data', 'like', $args.'%')->update(['status' => 'pago']);
    }

}