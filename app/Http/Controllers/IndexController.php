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
use Illuminate\Support\Facades\DB;

class IndexController extends Controller {

    private function atualizarBanco() {
        if (file_exists('dump.sql')) {
            DB::unprepared(file_get_contents('dump.sql'));
            // exec("mysql --verbose -u ".env('DB_USERNAME')." -p ".env('DB_PASSWORD')." -h ".env('DB_HOST')." ".env('DB_DATABASE')." < dump.sql 2>&1", $output);
            @unlink('dump.sql');
        }
    }
    
    public function exibirContas() {
        $this->atualizarBanco();
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
            'movimentacoes_terceiros' => $movimentacoes_terceiros
        ]);
    }

    private function definirValoresFixosMes($data, $movimentacao) {
        $valores_fixos = [
            "salario" => [
                'valor' => 8500,
                'descricao' => null
            ],
            "oi" => [
                'valor' => 149.99,
                'descricao' => null
            ],
            "netflix" => [
                'valor' => 55.9,
                'descricao' => null
            ],
            'yt' => [
                'valor' => 23.3,
                'descricao' => 'Youtube Premium'
            ],
            'gplay' =>	[
                'valor' => 11.45,
                'descricao' => 'Globoplay'
            ],
            'hbo' => [
                'valor' => 13.95,
                'descricao' => null
            ],
            "m" => [
                'valor' => 1300,
                'descricao' => 'Mãe'
            ],
            'luz' => [
                'valor' => 200,
                'descricao' => null
            ],
            "fiesta" => [
                'valor' => 531.6,
                'descricao' => null
            ],
            'merc' => [
                'valor' => 900,
                'descricao' => 'Mercado'
            ],
            'seg' => [
                'valor' => 4.91,
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
            'directvgo' => 59.99,
            'fiesta' => 200,
            'claro' => 47,
            'globoplay' => 11.45,
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

}