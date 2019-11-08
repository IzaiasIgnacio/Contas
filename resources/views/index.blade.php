<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Contas</title>
        <link rel="stylesheet" href="{{URL::asset('public/css/bootstrap/bootstrap.min.css')}}" />
        <link rel="stylesheet" href="{{URL::asset('public/css/fontawesome/fontawesome-all.css')}}" />
        <link rel="stylesheet" href="{{URL::asset('public/css/app.css')}}" />
        <script src="{{URL::asset('public/js/jquery.min.js')}}"></script>
        <script src="{{URL::asset('public/js/bootstrap.min.js')}}"></script>
        <script type="text/javascript">
            $().ready(function() {
                $(".div_movimentacoes").on('click','.tabela_mes thead',function() {
                    $("#modal_movimentacao").modal('show');
                });
                $(".salvar").click(function() {
                    $.post("{{route('salvar_movimentacao')}}", {dados: $("form").serialize()});
                    $("#modal_movimentacao").modal('hide');
                });
            });
        </script>
    </head>
    <body>
        <nav class="navbar navbar-inverse navbar-fixed-top">
            <div class="container menu_container">
                <ul class="nav navbar-brand">
                    <li>Contas</li>
                </ul>
                <div class="navbar-collapse collapse">
                    <ul class="nav navbar-nav">
                        <li><span class="valores_topo"><img src="{{URL::asset('public/imagens/casa.png')}}" /> R$ {{$helper->format($consolidado->where('nome', 'casa')->first()->valor)}}</span></li>
                        <li><span class="valores_topo"><img src="{{URL::asset('public/imagens/itau.png')}}" /> R$ {{$helper->format($consolidado->where('nome', 'itau')->first()->valor)}}</span></li>
                        <li><span class="valores_topo"><img src="{{URL::asset('public/imagens/inter.png')}}" /> R$ {{$helper->format($consolidado->where('nome', 'inter')->first()->valor)}}</span></li>
                        <li class="divisor">&nbsp;</li>
                        <li><span class="valores_topo"><img src="{{URL::asset('public/imagens/safe.png')}}" /> R$ {{$helper->format($consolidado->where('nome', 'savings')->first()->valor)}}</span></li>
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
                                                    $helper->format($movimentacoes
                                                        ->where('id_cartao', $cartao->id)
                                                        ->whereIn('status', ['normal', 'definido'])
                                                        ->where('tipo', 'gasto')
                                                            ->sum('valor'))
                                                    }} / {{$helper->format($cartao->credito)}}<br>{{$cartao->numero}}
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
            <div class="modal fade" id="modal_movimentacao" role="dialog" tabindex="-1">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"></span></button>
                            <h4 class="modal-title">Movimentação</h4>
                        </div>
                        <div class="modal-body row">
                            @include('form_movimentacao')
                        </div>
                        <div class="modal-footer footer_form_movimentacao">
                            <button type="button" class="btn btn-primary salvar">Salvar</button>
                            <button type="button" class="btn btn-primary cancelar" data-dismiss="modal">Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row div_movimentacoes">
                @for ($m=0;$m<=5;$m++)
                    <div class="col-md-2">
                        <table class="table table-condensed table-bordered tabela_mes">
                            <thead>
                                <tr>
                                    <th>{{$movimentacoes_mes[$m]['mes']}}<input type="hidden" class="mes_clicado" value="@Model.Indice"></th>
                                    @if ($m == 0)
                                        <th class="text-right">{{$helper->format($total_atual)}}</th>
                                    @else
                                        @php $total_atual = $consolidado->where('nome', 'salario')->first()->valor+$sobra @endphp
                                        <th class="text-right">{{$helper->format($total_atual)}}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @if (count($movimentacoes_mes[$m]['movimentacoes']) > 0)
                                    @php
                                        $total_mes = 0;
                                        $renda_mes = 0;
                                    @endphp
                                    @for ($i=0;$i<$maximo_movimentacoes;$i++)
                                        @isset($movimentacoes_mes[$m]['movimentacoes'][$i])
                                            <tr>
                                                <td><input type="hidden" class="id_movimentacao" value="@Model.Movimentacoes[i].Id" />{{$movimentacoes_mes[$m]['movimentacoes'][$i]->nome}}</td>
                                                <td class="text-right td_valor td_@Model.Movimentacoes[i].Status td_@Model.Movimentacoes[i].Tipo">{{$helper->format($movimentacoes_mes[$m]['movimentacoes'][$i]->valor)}}</td>
                                            </tr>
                                            @php
                                                if ($movimentacoes_mes[$m]['movimentacoes'][$i]->status != 'pago') {
                                                    if ($movimentacoes_mes[$m]['movimentacoes'][$i]->tipo == 'gasto') {
                                                        $total_mes += $movimentacoes_mes[$m]['movimentacoes'][$i]->valor;
                                                    }
                                                    if ($movimentacoes_mes[$m]['movimentacoes'][$i]->tipo == 'renda') {
                                                        $renda_mes += $movimentacoes_mes[$m]['movimentacoes'][$i]->valor;
                                                    }
                                                }
                                            @endphp
                                        @endisset
                                        @empty($movimentacoes_mes[$m]['movimentacoes'][$i])
                                            <tr>
                                                <td>&nbsp;</td>
                                                <td class="text-right">&nbsp;</td>
                                            </tr>
                                        @endempty
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
                                    @php
                                        if ($m == 0) {
                                            $sobra = str_replace(",","",$total_atual)-$total_mes-$renda_mes-$movimentacoes_mes[$m]['save']->valor;
                                        }
                                        else {
                                            $sobra = str_replace(",","",$total_atual)-($total_mes-$renda_mes);
                                            $save_mes[$m] = $helper->saveMes($sobra, $consolidado->where('nome', 'mensal')->first()->valor, $m);
                                        }
                                    @endphp
                                    @if ($m == 0)
                                        <td class="text-right save_@Model.Indice">{{$helper->format($movimentacoes_mes[$m]['save']->valor)}}</td>
                                    @else
                                        <td class="text-right save_@Model.Indice">{{$helper->format($save_mes[$m])}}</td>
                                    @endif
                                </tr>
                                <tr>
                                    <td>Total</td>
                                    <td class="text-right"><span class="valor_total">{{$helper->format($total_mes-$renda_mes)}}</span></td>
                                </tr>
                                <tr>
                                    <td>Sobra</td>
                                    <td class="text-right"><span class="valor_sobra">{{$helper->format($sobra)}}</span></td>
                                </tr>
                                @if ($m > 0)
                                    <tr>
                                        <td>reserva</td>
                                        <td class="text-right"><span class="valor_sobra">{{$helper->format($total_atual-($total_mes-$renda_mes)-$save_mes[$m])}}</span></td>
                                    </tr>
                                    <tr>
                                        <td>dif. salario</td>
                                        <td class="text-right"><span class="valor_sobra">{{$helper->format($consolidado->where('nome', 'salario')->first()->valor-($total_mes-$renda_mes))}}</span></td>
                                    </tr>
                                @endif
                            </tfoot>
                        </table>
                    </div>
                @endfor
                @for ($s=0;$s<=5;$s++)
                    @php
                        if ($s == 0) {
                            $savings_mes[$s] = $consolidado->where('nome', 'savings')->first()->valor+@$movimentacoes_mes[$s]['save']->valor;
                        }
                        else {
                            $savings_mes[$s] = $savings_mes[$s-1]+$save_mes[$s];
                        }
                    @endphp
                    <div class="col-md-2 tabela_saving_@i">
                        <table class="table table-condensed table-bordered">
                            <thead>
                                <tr>
                                    <th>{{$movimentacoes_mes[$s]['mes']}}</th>
                                    @if ($s == 0)
                                        <th class="text-right">{{$helper->format($consolidado->where('nome', 'savings')->first()->valor)}}</th>
                                    @else
                                        <th class="text-right">{{$helper->format($savings_mes[$s-1])}}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <td>Save</td>
                                    @if ($s == 0)
                                        <td class="text-right">{{$helper->format($movimentacoes_mes[$s]['save']->valor)}}</td>
                                    @else
                                        <td class="text-right">{{$helper->format($save_mes[$s])}}</td>
                                    @endif
                                </tr>
                                <tr>
                                    <td>Total</td>
                                    <td class="text-right">{{$helper->format($savings_mes[$s])}}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endfor
            </div>
        </div>
    </body>
</html>
