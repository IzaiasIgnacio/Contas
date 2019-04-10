<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Contas</title>
        <link rel="stylesheet" href="{{URL::asset('public/css/bootstrap/bootstrap.min.css')}}" />
        <link rel="stylesheet" href="{{URL::asset('public/css/fontawesome/fontawesome-all.css')}}" />
        <link rel="stylesheet" href="{{URL::asset('public/css/app.css')}}" />
    </head>
    <body>
        <nav class="navbar navbar-inverse navbar-fixed-top">
            <div class="container menu_container">
                <ul class="nav navbar-brand">
                    <li>Contas</li>
                </ul>
                <div class="navbar-collapse collapse">
                    <ul class="nav navbar-nav">
                        <li><span class="valores_topo"><img src="{{URL::asset('public/imagens/casa.png')}}" /> R$ {{$consolidado->where('nome', 'casa')->first()->valor}}</span></li>
                        <li><span class="valores_topo"><img src="{{URL::asset('public/imagens/itau.png')}}" /> R$ {{$consolidado->where('nome', 'itau')->first()->valor}}</span></li>
                        <li><span class="valores_topo"><img src="{{URL::asset('public/imagens/inter.png')}}" /> R$ {{$consolidado->where('nome', 'inter')->first()->valor}}</span></li>
                        <li class="divisor">&nbsp;</li>
                        <li><span class="valores_topo"><img src="{{URL::asset('public/imagens/safe.png')}}" /> R$ {{$consolidado->where('nome', 'savings')->first()->valor}}</span></li>
                        <li class="divisor">&nbsp;</li>
                        @foreach ($cartoes as $cartao)
                            <li>
                                <div class="row">
                                    <div class="col-md-4 topo_cartoes">
                                        <div class="col-md-12">
                                            <span class="valores_topo"><img src="{{URL::asset('public/imagens/'.$cartao->nome.'.png')}}" /></span>
                                        </div>
                                    </div>
                                    <div class="col-md-8 topo_cartoes col_valores">
                                        <div class="col-md-12 topo_cartoes">
                                            <span class="valores_topo">
                                                R$ {{
                                                    $movimentacoes
                                                        ->where('id_cartao', $cartao->id)
                                                        ->whereIn('status', ['normal', 'definido'])
                                                        ->where('tipo', 'gasto')
                                                            ->sum('valor')
                                                    }} / {{$cartao->credito}}<br>{{$cartao->numero}}
                                            </span>
                                        </div>
                                    </div>  
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    &nbsp;
                    &nbsp;
                    <i class="fa fa-table fa-inverse fa-lg" id="exportar" style="margin-top: 22px; cursor: pointer"></i>
                    <i class="fa fa-circle-notch fa-inverse slow-spin fa-2x fa-fw" style="display: none"></i>
                </div>
            </div>
        </nav>
        <div class="container body-content">
            <div class="row div_movimentacoes">
                @foreach ($movimentacoes_mes as $mes)
                    <div class="col-md-2">
                        <table class="table table-condensed table-bordered tabela_mes">
                            <thead>
                                <tr>
                                    <th>Mês<input type="hidden" class="mes_clicado" value="@Model.Indice"></th>
                                    <th class="text-right">{{$mes['mes']}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (count($mes['movimentacoes']) > 0)
                                    @php $total = 0; @endphp
                                    @for ($i=0;$i<$maximo_movimentacoes;$i++)
                                        <tr>
                                            <td><input type="hidden" class="id_movimentacao" value="@Model.Movimentacoes[i].Id" />{{$mes['movimentacoes'][$i]->nome}}</td>
                                            <td class="text-right td_valor td_@Model.Movimentacoes[i].Status td_@Model.Movimentacoes[i].Tipo">{{$mes['movimentacoes'][$i]->valor}}</td>
                                        </tr>
                                        @php $total += $mes['movimentacoes'][$i]->valor; @endphp
                                    @endfor
                                @endif
                                <tr>
                                    <td>&nbsp;</td>
                                    <td class="text-right">&nbsp;</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td><input type="hidden" class="id_movimentacao" value="@Model.SaveId" />Save</td>
                                    <td class="text-right save_@Model.Indice">Save</td>
                                </tr>
                                <tr>
                                    <td>Total</td>
                                    <td class="text-right"><span class="valor_total">{{$total}}</span></td>
                                </tr>
                                <tr>
                                    <td>Sobra</td>
                                    <td class="text-right"><span class="valor_sobra">Sobra</span></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endforeach
                @for ($i=0;$i<=5;$i++)
                    <div class="col-md-2 tabela_saving_@i">
                        <table class="table table-condensed table-bordered">
                            <thead>
                                <tr>
                                    <th>Mes</th>
                                    <th class="text-right">ValorMes</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <td>Save</td>
                                    <td class="text-right">Save</td>
                                </tr>
                                <tr>
                                    <td>Total</td>
                                    <td class="text-right">Sobra</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endfor
            </div>
        </div>
    </body>
</html>
