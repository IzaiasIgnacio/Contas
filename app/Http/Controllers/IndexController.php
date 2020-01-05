<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Cartao;
use App\Models\Consolidado;
use App\Models\Movimentacao;
use App\Models\Status;
use App\Models\Responsavel;
use App\Models\Tipo;
use Symfony\Component\HttpFoundation\Request;

class IndexController extends Controller {

    private function atualizarBanco() {
        if (file_exists('dump.sql')) {
            exec("mysql -u3280436_contas -pa9TUW813KliNIe -hfdb24.awardspace.net 3280436_contas < dump.sql");
            unlink('dump.sql');
        }
    }
    public function exibirContas() {
        // $this->atualizarBanco();
        $cartao = new Cartao();
        $consolidado = new Consolidado();
        $movimentacao = new Movimentacao();
        $movimentacoes_mes = array();
        $maximo_movimentacoes = 0;
        $maximo_movimentacoes_terceiros = 0;
        
        $data = \Carbon\Carbon::createFromFormat('d/m/Y', '01/'.$consolidado->where('nome', 'mes_atual')->first()->valor);

        for ($i=0;$i<=6;$i++) {
            $this->definirValoresFixosMes($data, $movimentacao);
            $movimentacoes_mes[$i] = [
                'mes' => ucfirst($data->locale('pt-br')->monthName),
                'numero_mes' => $data->locale('pt-br')->month,
                'ano' => $data->locale('pt-br')->year,
                'movimentacoes' => $movimentacao
                                    ->whereMonth('data', $data->format('m'))
                                    ->whereYear('data', $data->format('Y'))
                                    ->whereNotIN('tipo', ['save', 'terceiros'])
                                        ->orderBy('tipo')
                                        ->orderBy('posicao')
                                            ->get(),
                'save' => $movimentacao->whereMonth('data', $data->format('m'))->whereYear('data', $data->format('Y'))->where('tipo', 'save')->first()
            ];
            
            if (count($movimentacoes_mes[$i]['movimentacoes']) > $maximo_movimentacoes) {
                $maximo_movimentacoes = count($movimentacoes_mes[$i]['movimentacoes']);
            }

            $movimentacoes_terceiros[$i] = $movimentacao
                                            ->whereMonth('data', $data->format('m'))
                                            ->whereYear('data', $data->format('Y'))
                                            ->where('tipo', 'terceiros')
                                                ->orderBy('tipo')
                                                ->orderBy('posicao')
                                                    ->get();
            if (count($movimentacoes_terceiros[$i]) > $maximo_movimentacoes_terceiros) {
                $maximo_movimentacoes_terceiros = count($movimentacoes_terceiros[$i]);
            }
            $data->addMonth();
        }
        
        $total_atual = $consolidado->where('nome', 'itau')->first()->valor + $consolidado->where('nome', 'casa')->first()->valor;
        
        return view('index', [
            'helper' => new \App\Models\Helper(),
            'tipos' => Tipo::get(),
            'lista_status' => Status::get(),
            'lista_responsavel' => Responsavel::get(),
            'total_atual' => number_format($total_atual, 2),
            'maximo_movimentacoes' => $maximo_movimentacoes,
            'maximo_movimentacoes_terceiros' => $maximo_movimentacoes_terceiros,
            'cartoes' => $cartao,
            'modelCartoes' => $cartao,
            'consolidado' => $consolidado,
            'movimentacoes' => $movimentacao->whereRaw("data >= '".$data->format('Y-m-d')."'")->get(),
            'total_movimentacoes' => $movimentacao,
            'movimentacoes_mes' => $movimentacoes_mes,
            'movimentacoes_terceiros' => $movimentacoes_terceiros
        ]);
    }

    private function definirValoresFixosMes($data, $movimentacao) {
        $valores_fixos = [
            "virtua" => [
                'valor' => 200,
                'descricao' => null
            ],
            "netflix" => [
                'valor' => 45.9,
                'descricao' => null
            ],
            'prime' => [
                'valor' => 9.9,
                'descricao' => 'Amazon Prime'
            ],
            'gpm' => [
                'valor' => 16.9,
                'descricao' => 'Google Play Music'
            ],
            'gp' =>	[
                'valor' => 13.99,
                'descricao' => 'Xbox Game Pass'
            ],
            "m" => [
                'valor' => 1500,
                'descricao' => 'Mãe'
            ],
            "fiesta" => [
                'valor' => 531.6,
                'descricao' => null
            ],
            'merc' => [
                'valor' => 750,
                'descricao' => 'Mercado'
            ],
            "vivo" => [
                'valor' => 49.99,
                'descricao' => null
            ],
            'seg' => [
                'valor' => 4.49,
                'descricao' => 'Seguro Cartão Itaú'
            ]
        ];

        $p = 1;
        foreach ($valores_fixos as $nome => $valores) {
            if ($movimentacao->whereRaw("data = '".$data->format('Y-m-d')."'")->where('nome', $nome)->where('tipo', 'gasto')->count() == 0) {
                $mov = new Movimentacao();
                $mov->nome = $nome;
                $mov->descricao = $valores['descricao'];
                $mov->valor = $valores['valor'];
                $mov->tipo = 'gasto';
                $mov->data = $data->format('Y-m-d');
                $mov->status = 'definido';
                $mov->posicao = $p;
                $mov->save();
                $p++;
            }
        }

        $valores_fixos = [
            'net' => 110,
            'fone' => null,
            'fiesta' => 200,
            'claro' => 39.99,
            'nubank' => null,
            'luz' => null
        ];

        $p = 1;
        foreach ($valores_fixos as $nome => $valor) {
            if ($movimentacao->whereRaw("data = '".$data->format('Y-m-d')."'")->where('nome', $nome)->where('tipo', 'terceiros')->count() == 0) {
                $mov = new Movimentacao();
                $mov->nome = $nome;
                $mov->valor = $valor;
                $mov->tipo = 'terceiros';
                $mov->data = $data->format('Y-m-d');
                $mov->status = 'definido';
                $mov->responsavel = 'mae';
                $mov->posicao = $p;
                $mov->save();
                $p++;
            }
        }
    }

    public function salvarConsolidado(Request $request) {
        $consolidado = Consolidado::where('nome', $request['tipo'])->first();
        if ($request['tipo'] != 'mes_atual') {
            $consolidado->valor = str_replace(",", ".", $request['valor']);
        }
        else {
            $consolidado->valor = $request['valor'];
        }
        $consolidado->valor = str_replace(",", ".", $request['valor']);
        $consolidado->save();
    }
    
    public function salvarMovimentacao(Request $request) {
        $cartao = Cartao::find($request['cartao']);
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
            $nome = $request['nome'];
            if ($request['parcelas'] > 1) {
                $nome = $request['nome']." ".$p."/".$request['parcelas'];
            }
            $movimentacao->nome = $nome;
            if ($p > 1) {
                $data = date_add($data, date_interval_create_from_date_string("1 month"));
            }
            $movimentacao->descricao = $request['descricao'];
            $movimentacao->data = $data->format('Y-m-d');
            $movimentacao->tipo = $request['tipo'];
            $movimentacao->valor = $request['valor'];
            $movimentacao->status = $request['status'];
            $movimentacao->responsavel = $request['responsavel'];
            $movimentacao->id_cartao = $id_cartao;
            $movimentacao->posicao = 999;
            $movimentacao->save();
        }
    }

    public function atualizarMovimentacao(Request $request) {
        $movimentacao = Movimentacao::find($request['id']);
        switch ($request['valor']) {
            case 'planejado':
            case 'definido':
            case 'pago':
                $movimentacao->status = $request['valor'];
            break;
            case 'gasto';
            case 'renda';
            case 'terceiros';
                $movimentacao->tipo = $request['valor'];
            break;
            default:
                if ($request['valor'] == 'nenhum') {
                    $movimentacao->id_cartao = null;
                }
                else {
                    $movimentacao->id_cartao = Cartao::where('nome', $request['valor'])->first()->id;
                }
            break;
        }
        $movimentacao->save();
    }

    public function atualizarValorMovimentacao(Request $request) {
        $movimentacao = Movimentacao::find($request['id']);
        $movimentacao->valor = $request['valor'];
        $movimentacao->save();
    }

    public function atualizarNomeMovimentacao(Request $request) {
        $movimentacao = Movimentacao::find($request['id']);
        $movimentacao->nome = $request['nome'];
        $movimentacao->save();
    }

    public function getNomeMovimentacao(Request $request) {
        return Movimentacao::find($request['id'])->nome;
    }

    public function ExcluirMovimentacao(Request $request) {
        Movimentacao::find($request['id'])->delete();
    }

    public function definirItau(Request $request) {
        $mov = Movimentacao::find($request['id']);
        $mov->itau = !$mov->itau;
        $mov->save();
    }

    public function AtualizarSave(Request $request) {
        $movimentacao = Movimentacao::where(
            [
                'nome' => 'save',
                'data' => date('Y').'-'.$request['mes'].'-01'
            ]
        )->first();

        if ($movimentacao == null) {
            $movimentacao = new Movimentacao();
        }

        $movimentacao->nome = 'save';
        $movimentacao->data = date('Y').'-'.$request['mes'].'-01';
        $movimentacao->tipo = 'save';
        $movimentacao->valor = $request['valor'];

        $movimentacao->save();
    }

    public function AtualizarPosicoes(Request $request) {
        if (isset($request['gastos'])) {
            for ($i=0;$i<count($request['gastos']);$i++) {
                $movimentacao = Movimentacao::find($request['gastos'][$i]);
                $movimentacao->posicao = $i+1;
                $movimentacao->save();
            }
        }
        if (isset($request['rendas'])) {
            for ($i=0;$i<count($request['rendas']);$i++) {
                $movimentacao = Movimentacao::find($request['rendas'][$i]);
                $movimentacao->posicao = $i+1;
                $movimentacao->save();
            }
        }
        if (isset($request['terceiros'])) {
            for ($i=0;$i<count($request['terceiros']);$i++) {
                $movimentacao = Movimentacao::find($request['terceiros'][$i]);
                $movimentacao->posicao = $i+1;
                $movimentacao->save();
            }
        }
    }

    public function exibirExtra(Request $request) {
        $d = explode(".", $request['data']);
        $data = \Carbon\Carbon::createFromFormat('d/m/Y', '01/'.$d[0]."/".$d[1]);

        $helper = new \App\Models\Helper();
        $movimentacao = new Movimentacao();
        $responsaveis = Responsavel::get();
        $responsaveis['izaias'] = 'Izaias';
        $gastos = [];
        $total = 0;
        $total_com_atrasado = 0;
        $total_chah = 0;

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
        $deposito = new Movimentacao();
        $deposito->nome = 'deposito';
        $deposito->valor = 170;
        $deposito->tipo = 'renda';
        $itau[] = $deposito;
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
            if ($movimentacao->responsavel == 'chah') {
                $total_chah += $movimentacao->valor;
            }
            else {
                $total += $movimentacao->valor;
            }
        }

        $antigo_chah = new Movimentacao();
        $antigo_chah->nome = 'Antigo';
        $antigo_chah->valor = 348.54;
        $gastos['chah'][] = $antigo_chah;

        $mes = new Movimentacao();
        $mes->nome = 'mês';
        $mes->valor = 1300;
        $gastos['izaias'][] = $mes;
        $total -= $mes->valor;

        $pago = new Movimentacao();
        $pago->nome = 'pago';
        $pago->valor = 1866.61;
        $gastos['mae'][] = $pago;
        $total -= $pago->valor;
        
        foreach ($izaias as $i) {
            $gastos['izaias'][] = $i;
            $total -= $i->valor;
        }
        
        return view('extra', [
            'helper' => $helper,
            'responsaveis' => $responsaveis,
            'gastos' => $gastos,
            'total' => $total,
            'total_chah' => $total_chah,
            'itau' => $itau,
            'total_itau' => $total_itau,
            'valor_itau' => $valor_itau
        ]);
    }

}