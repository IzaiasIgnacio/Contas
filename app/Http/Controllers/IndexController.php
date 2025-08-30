<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Cartao;
use App\Models\Consolidado;
use App\Models\Helper;
use App\Models\Movimentacao;
use App\Models\Status;
use App\Models\Responsavel;
use App\Models\Tipo;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Support\Facades\DB;

class IndexController extends Controller {

    private function atualizarBanco() {
        if (file_exists('dump.sql')) {
            DB::unprepared(file_get_contents('dump.sql'));
            // exec("mysql --verbose -u ".env('DB_USERNAME')." -p ".env('DB_PASSWORD')." -h ".env('DB_HOST')." ".env('DB_DATABASE')." < dump.sql 2>&1", $output);
            @unlink('dump.sql');
        }
    }

    public function exibirLayout() {
        return view('layout');
    }
    
    public function exibirContas() {
        $this->atualizarBanco();
        $cartao = new Cartao();
        $consolidado = new Consolidado();
        $movimentacao = new Movimentacao();
        $movimentacoes_mes = array();
        $maximo_movimentacoes = 0;
        $maximo_movimentacoes_terceiros = 0;
        
        date_default_timezone_set('America/Sao_Paulo');
        $data = \Carbon\Carbon::createFromFormat('d/m/Y', '01/'.$consolidado->where('nome', 'mes_atual')->first()->valor);
        $dia = date('d');
        
        if (date('m') != $data->month) {
            $dia = 0;
        }

        for ($i=0;$i<=6;$i++) {
            $this->definirValoresFixosMes($data, $movimentacao);
            $movimentacoes_mes[$i] = [
                'mes' => ucfirst($data->locale('pt-br')->monthName),
                'numero_mes' => $data->locale('pt-br')->month,
                'ano' => $data->locale('pt-br')->year,
                'movimentacoes' => $movimentacao
                                    ->whereMonth('data', $data->format('m'))
                                    ->whereYear('data', $data->format('Y'))
                                    ->whereNotIn('tipo', ['save', 'terceiros'])
                                    ->where('nome', '!=', 'salario')
                                        ->orderBy('tipo')
                                        ->orderBy('posicao')
                                            ->get(),
                'save' => $movimentacao->whereMonth('data', $data->format('m'))->whereYear('data', $data->format('Y'))->where('tipo', 'save')->first(),
                'salario' => $movimentacao->whereMonth('data', $data->format('m'))->whereYear('data', $data->format('Y'))->where('nome', 'salario')->first()
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
        
        $save = [
            2 => 60,
            3 => 170,
            4 => 160,
            5 => 150,
            6 => 140,
            7 => 130,
            8 => 120,
            9 => 110,
            10 => 100,
            11 => 90,
            12 => 80,
            1 => 70
        ];

        return view('index', [
            'helper' => new \App\Models\Helper(),
            'tipos' => Tipo::get(),
            'lista_status' => Status::get(),
            'lista_responsavel' => Responsavel::get(),
            'total_atual' => number_format($total_atual, 2),
            'maximo_movimentacoes' => $maximo_movimentacoes,
            'maximo_movimentacoes_terceiros' => $maximo_movimentacoes_terceiros,
            'cartoes_topo' => $cartao->where('ativo', 1)->where('nome', '!=', 'nubankMae')->orderBy('ordem')->get(),
            'cartoes' => $cartao->where('ativo', 1),
            'modelCartoes' => $cartao,
            'consolidado' => $consolidado,
            'movimentacoes' => $movimentacao->whereRaw("data >= '".$data->format('Y-m-d')."'")->get(),
            'total_movimentacoes' => $movimentacao,
            'movimentacoes_mes' => $movimentacoes_mes,
            'movimentacoes_terceiros' => $movimentacoes_terceiros,
            'saldo_final' => $this->calculoMesAtual(),
            'objetivo' => (1000/30)*(30-$dia),
            'proximo_mes' => date('m', strtotime('first day of +1 month')),
            'meses' => $this->meses(),
            'save' => $save
        ]);
    }

    private function calculoMesAtual() {
        $movimentacao = new Movimentacao();
        $helper = new Helper();
        $data = \Carbon\Carbon::createFromFormat('d/m/Y', '01/'.Consolidado::where('nome', 'mes_atual')->first()->valor);

        $saldo_atual = Consolidado::where('totais', 1)->sum('valor');
        $saldo_final = 0;
        
        $renda = $movimentacao->whereMonth('data', $data->format('m'))->whereYear('data', $data->format('Y'))->where('tipo', 'renda')->where('status', '<>', 'pago')->sum('valor');
        $gastos = $movimentacao->whereMonth('data', $data->format('m'))->whereYear('data', $data->format('Y'))->where('tipo', 'gasto')->where('status', '<>', 'pago')->sum('valor');
        
        $saldo_final = $saldo_atual - $gastos + $renda;

        return $helper->format($saldo_final);
    }

    private function meses() {
        $consolidado = new Consolidado();
        $movimentacao = new Movimentacao();
        $helper = new Helper();
        $meses = [];

        date_default_timezone_set('America/Sao_Paulo');
        $total_atual = Consolidado::where('totais', 1)->sum('valor');
        $data = \Carbon\Carbon::createFromFormat('d/m/Y', '01/'.$consolidado->where('nome', 'mes_atual')->first()->valor);

        for ($i=0;$i<=6;$i++) {
            $renda = $movimentacao->whereMonth('data', $data->format('m'))->whereYear('data', $data->format('Y'))->where('tipo', 'renda')->where('status', '<>', 'pago')->sum('valor');
            $gastos = $movimentacao->whereMonth('data', $data->format('m'))->whereYear('data', $data->format('Y'))->where('tipo', 'gasto')->where('status', '<>', 'pago')->sum('valor');
            $saldo = $total_atual - $gastos + $renda;
            $renda_cdb = $saldo * 0.009;

            if ($i == 0) {
                if ($data->format('m') == date('m')) {
                    $dias = 30 - date('d');
                    $renda_cdb = ($renda_cdb / 30) * $dias;
                }
            }

            $meses[$i] = [
                'saldo' => $helper->format($saldo),
                'renda' => $renda_cdb
            ];

            $total_atual = $saldo + $renda_cdb;
            $data->addMonth();
            // if ($i == 1) {
            //     echo $renda.PHP_EOL;
            //     echo $gastos.PHP_EOL;
            //     echo $saldo.PHP_EOL;
            //     print_r($meses);
            //     die;
            // }            
        }
        
        return $meses;
    }

    public function definirValoresFixosMes($data, $movimentacao) {
        $valores_fixos = [
            // "salario" => [
            //     'valor' => Consolidado::where('nome', 'salario')->first()->valor,
            //     'descricao' => null
            // ],
            "claro" => [
                'valor' => 79.9,
                'descricao' => null
            ],
            "netflix" => [
                'valor' => 59.9,
                'descricao' => null
            ],
            'google' => [
                'valor' => 33.29,
                'descricao' => 'Youtube Premium'
            ],
            'gplay' =>	[
                'valor' => 6.45,
                'descricao' => 'Globoplay'
            ],
            'meli' => [
                'valor' => 9.90,
                'descricao' => null
            ],
            "m" => [
                'valor' => 1800,
                'descricao' => 'Mãe'
            ],
            'luz' => [
                'valor' => 300,
                'descricao' => null
            ],
            'merc' => [
                'valor' => 1300,
                'descricao' => 'Mercado'
            ],
            'klini' => [
                'valor' => 302,
                'descricao' => 'Saúde'
            ],
            'seg' => [
                'valor' => 5.85,
                'descricao' => 'Seguro Cartão Itaú'
            ]
        ];

        $p = 1;
        foreach ($valores_fixos as $nome => $valores) {
            if ($movimentacao->whereRaw("data = '".$data->format('Y-m-d')."'")->where('nome', $nome)->whereIn('tipo', ['gasto', 'renda'])->count() == 0) {
                $mov = new Movimentacao();
                $mov->nome = $nome;
                $mov->descricao = $valores['descricao'];
                $mov->valor = $valores['valor'];
                $mov->tipo = ($nome != 'salario') ? 'gasto' :  'renda';
                $mov->data = $data->format('Y-m-d');
                $mov->status = 'definido';
                $mov->posicao = ($nome != 'salario') ? 0 : $p;
                $mov->save();
                if ($nome != 'salario') {
                    $p++;
                }
            }
        }

        $valores_fixos = [
            'klini' => 302,
            'sky' => 84.9,
            'vivo' => 44.42,
            'globoplay' => 6.45,
            'youtube' => 8.6,
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

        $valores_fixos = [
            'google' => 4
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
                $mov->responsavel = 'chah';
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

    public function salvarSavings(Request $request) {
        $nubank = floatval(str_replace(",", ".", $request['nubank']));
        $caixinha = floatval(str_replace(",", ".", $request['caixinha']));
        $caixinha2 = floatval(str_replace(",", ".", $request['caixinha2']));
        $mp = floatval(str_replace(",", ".", $request['mp']));
        $casa = floatval(str_replace(",", ".", $request['casa']));
        $itau = floatval(str_replace(",", ".", $request['itau']));
        $iti = floatval(str_replace(",", ".", $request['iti']));
        $cofrinho = floatval(str_replace(",", ".", $request['cofrinho']));
        $savings = number_format($nubank + $caixinha2 + $mp + $casa + $itau + $iti + $cofrinho, 2, '.', '');

        Consolidado::where('nome', 'nubank')->update(['valor' => $nubank]);
        Consolidado::where('nome', 'caixinha')->update(['valor' => $caixinha]);
        Consolidado::where('nome', 'caixinha2')->update(['valor' => $caixinha2]);
        Consolidado::where('nome', 'mp')->update(['valor' => $mp]);
        Consolidado::where('nome', 'casa')->update(['valor' => $casa]);
        Consolidado::where('nome', 'itau')->update(['valor' => $itau]);
        Consolidado::where('nome', 'iti')->update(['valor' => $iti]);
        Consolidado::where('nome', 'cofrinho')->update(['valor' => $cofrinho]);
        Consolidado::where('nome', 'savings')->update(['valor' => $savings]);
    }
    
    public function salvarMovimentacao(Request $request) {
        $cartao = Cartao::find($request['cartao']);
        $id_cartao = null;
        if ($cartao != null) {
            $id_cartao = $cartao->id;
        }

        $id_parcela = null;
        if ($request['parcelas'] == '') {
            $request['parcelas'] = 1;
        }
        else {
            $id_parcela = time();
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
            $movimentacao->id_parcela = $id_parcela;
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
                if ($request['valor'] == 'pago') {
                    $movimentacao->itau = false;
                }
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
        $movimentacao = Movimentacao::find($request['id']);
        if (empty($movimentacao->id_parcela)) {
            $movimentacao->delete();
        }
        else {
            $parcelas = Movimentacao::where('id_parcela', $movimentacao->id_parcela)->get();
            foreach ($parcelas as $parcela) {
                $parcela->delete();
            }
        }
    }

    public function definirItau(Request $request) {
        $mov = Movimentacao::find($request['id']);
        $mov->itau = !$mov->itau;
        if ($mov->itau) {
            $mov->nb = false;
        }
        $mov->save();
    }

    public function definirNubank(Request $request) {
        $mov = Movimentacao::find($request['id']);
        $mov->nb = !$mov->nb;
        if ($mov->nb) {
            $mov->itau = false;
        }
        $mov->save();
    }

    public function definirIti(Request $request) {
        $mov = Movimentacao::find($request['id']);
        $this->definirNenhum($mov, 'iti');
        $mov->iti = !$mov->iti;
        $mov->save();
    }

    private function definirNenhum($movimentacao, $atual) {
        $movimentacao->itau = false;
        $movimentacao->nb = false;
        if ($atual != 'iti') {
            $movimentacao->iti = false;
        }
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

}