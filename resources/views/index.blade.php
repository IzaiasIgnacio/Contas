<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Contas</title>
        <link rel="stylesheet" href="{{URL::asset('public/css/bootstrap/bootstrap.min.css')}}" />
        <link rel="stylesheet" href="{{URL::asset('public/css/fontawesome/fontawesome-4.7.0.css')}}" />
        <link rel="stylesheet" href="{{URL::asset('public/css/fontawesome/fontawesome-all.css')}}" />
        <link rel="stylesheet" href="{{URL::asset('public/css/jquery.contextMenu.min.css')}}" />
        <link rel="stylesheet" href="{{URL::asset('public/css/app.css')}}" />
        <script src="{{URL::asset('public/js/jquery.min.js')}}"></script>
        <script src="{{URL::asset('public/js/bootstrap.min.js')}}"></script>
        <script src="{{URL::asset('public/js/jquery.contextMenu.min.js')}}"></script>
        <script src="{{URL::asset('public/js/jquery.ui.position.min.js')}}"></script>
        <script src="{{URL::asset('public/js/html5sortable.min.js')}}"></script>
        <script type="text/javascript">
            var tipo_consolidado;
            var movimentacao_escolhida;

            $().ready(function() {
                $(".icone_consolidado").click(function() {
                    switch ($(this).attr('tipo')) {
                        case 'casa':
                            $(".titulo_consolidado_modal").html('Casa');
                            $(".valor_consolidado_modal").val("{{$consolidado::where('nome', 'casa')->first()->valor}}");
                        break;
                        case 'itau':
                            $(".titulo_consolidado_modal").html('Itaú');
                            $(".valor_consolidado_modal").val("{{$consolidado::where('nome', 'itau')->first()->valor}}");
                        break;
                        case 'savings':
                            $(".titulo_consolidado_modal").html('Savings');
                            $(".valor_consolidado_modal").val("{{$consolidado::where('nome', 'savings')->first()->valor}}");
                        break;
                        case 'mes_atual':
                            $(".titulo_consolidado_modal").html('Mês Atual');
                            $(".valor_consolidado_modal").val("{{$consolidado::where('nome', 'mes_atual')->first()->valor}}");
                        break;
                    }

                    tipo_consolidado = $(this).attr('tipo');
                    $("#modal_consolidado").modal('show');
                });

                $('#modal_consolidado').on('shown.bs.modal', function (e) {
                    $(".valor_consolidado_modal").focus();
                    $(".valor_consolidado_modal").select();
                });

                $('#modal_movimentacao').on('shown.bs.modal', function (e) {
                    $("#nome").focus();
                    $("#nome").select();
                });

                $(".div_movimentacoes").on('click','.tabela_mes thead .fa-plus-square',function() {
                    var d = new Date();
                    var dia = d.getDate();
                    var mes = $(this).next('.mes_clicado').val();
                    var ano = $(this).next().next('.ano_clicado').val();
                    $("#modal_movimentacao #data").val(dia+"/"+mes+"/"+ano);

                    $("#modal_movimentacao").modal('show');
                });

                $("#modal_consolidado .salvar").click(function() {
                    $.post("{{route('salvar_consolidado')}}", {tipo: tipo_consolidado, valor: $(".valor_consolidado_modal").val()},
                    function(resposta) {
                        location.reload();
                    });
                });

                $("#modal_movimentacao .salvar").click(function() {
                    $.post("{{route('salvar_movimentacao')}}", {
                        nome: $("#nome").val(),
                        descricao: $("#descricao").val(),
                        data: $("#data").val(),
                        tipo: $("#tipo").val(),
                        valor: $("#valor").val(),
                        status: $("#status").val(),
                        responsavel: $("#responsavel").val(),
                        cartao: $("#cartao").val(),
                        parcelas: $("#parcelas").val()
                    },
                    function(resposta) {
                        location.reload();
                    });
                });

                $.contextMenu({
                    selector: '.tabela_mes tbody td', 
                    events: {
                        show: function(options) {
                            movimentacao_escolhida = options.$trigger.parent().find('.id_movimentacao').val();
                        }
                    },
                    callback: function(key, options) {
                        switch (key) {
                            case 'excluir':
                                $.post("{{route('excluir_movimentacao')}}", {id: movimentacao_escolhida},
                                function(resposta) {
                                    location.reload();
                                });
                            break;
                            case 'itau':
                                $.post("{{route('definir_itau')}}", {id: movimentacao_escolhida},
                                function(resposta) {
                                    location.reload();
                                });
                            break;
                            case 'descricao':
                            break;
                            default:
                                $.post("{{route('atualizar_movimentacao')}}", {id: movimentacao_escolhida, valor: key},
                                function(resposta) {
                                    location.reload();
                                });
                            break;
                        }
                    },
                    items: {
                        "planejado": {name: "Planejado", icon: "fa-square-o"},
                        "definido": {name: "Definido", icon: "fa-check-square-o"},
                        "pago": {name: "Pago", icon: "fa-check-square"},
                        "sep1": "---",
                        "gasto": {name: "Gasto", icon: "fa-minus"},
                        "renda": {name: "Renda", icon: "fa-plus"},
                        "terceiros": {name: "Terceiros", icon: "fa-user"},
                        "sep2": "---",
                        "cartao": {
                            "name": "Cartão", 
                            "icon": "fa-cc",
                            "items": {
                                "nenhum": {"name": "Nenhum"},
                                @foreach ($cartoes->get() as $cartao)
                                {{$cartao->nome}}: {"name": "{{$cartao->nome}}"},
                                @endforeach
                            }
                        },
                        "sep3": "---",
                        "itau": {name: "Itaú", icon: "fa-info-circle"},
                        "sep4": "---",
                        "descricao": {name: "Descrição", icon: "fa-edit"},
                        "sep5": "---",
                        "excluir": {name: "Excluir", icon: "fa-trash"},
                    }
                });

                $(".td_save").dblclick(function() {
                    $(this).html("<input type='text' id='save' style='height:20px;width:50px;color:#000' value="+$(this).html()+">");
                    $("#save").focus();
                    $("#save").select();
                });

                $(".td_valor").dblclick(function() {
                    movimentacao_escolhida = $(this).prev().prev().val();
                    $(this).html("<input type='text' id='novo_valor' style='height:20px;width:50px;color:#000' value="+$(this).html()+">");
                    $("#novo_valor").focus();
                    $("#novo_valor").select();
                });

                $(".td_nome_movimentacao").dblclick(function() {
                    movimentacao_escolhida = $(this).prev().val();
                    var element = $(this);
                    $.post("{{route('nome_movimentacao')}}", {id: movimentacao_escolhida},
                    function(resposta) {
                        element.html("<input type='text' id='novo_nome' style='height:20px;width:150px;color:#000' value='"+resposta+"'>");
                        $("#novo_nome").focus();
                        $("#novo_nome").select();
                    });
                });

                $(".td_save").on("keypress", "#save", function(e) {
                    if (e.which == 13) {
                        $.post("{{route('atualizar_save')}}", {mes: "{{$movimentacoes_mes[0]['numero_mes']}}", valor: $("#save").val()},
                        function(resposta) {
                            location.reload();
                        });
                    }
                });

                $(".td_valor").on("keypress", "#novo_valor", function(e) {
                    if (e.which == 13) {
                        $.post("{{route('atualizar_valor_movimentacao')}}", {id: movimentacao_escolhida, valor: $("#novo_valor").val()},
                        function(resposta) {
                            location.reload();
                        });
                    }
                });

                $(".td_nome_movimentacao").on("keypress", "#novo_nome", function(e) {
                    if (e.which == 13) {
                        $.post("{{route('atualizar_nome_movimentacao')}}", {id: movimentacao_escolhida, nome: $("#novo_nome").val()},
                        function(resposta) {
                            location.reload();
                        });
                    }
                });

                $("#tipo").change(function() {
                    if ($(this).val() == 'terceiros') {
                        $("#div_responsavel").fadeIn();
                    }
                    else {
                        $("#div_responsavel").fadeOut();
                    }
                });

                $("#exibir_terceiros").click(function() {
                    $(".tabela_terceiros").toggle();
                });

                sortable('.tabela_mes tbody', {
                    items: 'tr',
                    placeholder: "<tr><td colspan=2>&nbsp;</td></tr>",
                    forcePlaceholderSize: false
                });

                for (var i=0;i<=6;i++) {
                    sortable('.tabela_mes tbody')[i].addEventListener('sortupdate', updateFunction);
                    sortable('.tabela_terceiros tbody')[i].addEventListener('sortupdate', updateFunction);
                }

                function updateFunction(e) {
                    var gastos = [];
                    var rendas = [];
                    var terceiros = [];
                    $(this).find('tr').each(function(i, e) {
                        if ($(e).hasClass('linha_gasto')) {
                            gastos.push($(e).find('.id_movimentacao').val());
                        }
                        if ($(e).hasClass('linha_renda')) {
                            rendas.push($(e).find('.id_movimentacao').val());
                        }
                        if ($(e).hasClass('linha_terceiros')) {
                            terceiros.push($(e).find('.id_movimentacao').val());
                        }
                    });

                    $.post("{{route('atualizar_posicoes')}}", {
                        gastos: gastos,
                        rendas: rendas,
                        terceiros: terceiros,
                    });
                }

                $(function () {
                    $('[data-toggle="tooltip"]').tooltip({
                        trigger: "click"
                    });
                });

                $('[data-toggle="tooltip"]').on('shown.bs.tooltip', function () {
                    setTimeout(function() {
                        $('[data-toggle="tooltip"]').tooltip('hide');
                        }, 5000
                    );
                });

                $("#exportar").click(function() {
                    $(".fa-circle-notch").fadeIn();

                    $.post("{{route('exportar')}}", {},
                    function(resposta) {
                        $(".fa-circle-notch").fadeOut();
                    });
                });
            });
        </script>
    </head>
    <body>
        <nav class="navbar navbar-inverse navbar-static-top" @php if (getenv('DB_CONTAS') == 'contas_hmg') { echo "style='background-color: #950000'"; } @endphp>
            <div class="container menu_container">
                <ul class="nav navbar-brand">
                    <li>Contas</li>
                </ul>
                <div class="navbar-collapse collapse">
                    <ul class="nav navbar-nav">
                        <li><span class="valores_topo"><img class='icone_consolidado' tipo='mes_atual' src="{{URL::asset('public/imagens/calendar.png')}}" /> {{$consolidado::where('nome', 'mes_atual')->first()->valor}}</span></li>
                        <li><span class="valores_topo"><img class='icone_consolidado' tipo='casa' src="{{URL::asset('public/imagens/casa.png')}}" /> R$ {{$helper->format($consolidado->where('nome', 'casa')->first()->valor)}}</span></li>
                        <li><span class="valores_topo"><img class='icone_consolidado' tipo='itau' src="{{URL::asset('public/imagens/itau.png')}}" /> R$ {{$helper->format($consolidado->where('nome', 'itau')->first()->valor)}}</span></li>
                        <li class="divisor">&nbsp;</li>
                        <li><span class="valores_topo"><img class='icone_consolidado' tipo='savings' src="{{URL::asset('public/imagens/safe.png')}}" /> R$ {{$helper->format($consolidado->where('nome', 'savings')->first()->valor)}}</span></li>
                        <li class="divisor">&nbsp;</li>
                        @foreach ($cartoes->where('nome', '!=', 'nubankMae')->get() as $cartao)
                            <li>
                                <div class="row row_cartoes">
                                    <div class="col-md-4 topo_cartoes">
                                        <div class="col-md-12">
                                            <span class="valores_topo"><img src="{{URL::asset('public/imagens/'.$cartao->nome.'.png')}}" /></span>
                                        </div>
                                    </div>
                                    <div class="col-md-8 topo_cartoes col_valores">
                                        <div class="col-md-12 topo_cartoes">
                                            <span class="valores_topo">
                                                @php
                                                    $gastos_cartao =
                                                        $total_movimentacoes
                                                        ->where(function ($query) use ($cartao) {
                                                            $query->where('id_cartao', $cartao->id)
                                                                    ->where('status', 'definido')
                                                                    ->where('tipo', 'gasto');
                                                        })->orWhere(function($query) use ($cartao) {
                                                            $query->where('id_cartao', $cartao->id)
                                                                    ->where('status', '!=', 'pago')
                                                                    ->where('tipo', 'terceiros');
                                                        })
                                                        ->sum('valor');
                                                    if ($cartao->id == 1) { $gastos_cartao += 16.9+13.99+9.9; }
                                                @endphp
                                                R$ {{$helper->format($cartao->credito-$gastos_cartao)}} / {{$helper->format($cartao->credito)}}<br>{{$cartao->numero}}
                                            </span>
                                        </div>
                                    </div>  
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    &nbsp;
                    &nbsp;
                    <i class="fa fa-user fa-inverse fa-lg" id="exibir_terceiros" style="margin-top: 22px; cursor: pointer"></i>
                    <i class="fa fa-book fa-inverse fa-lg" id="exibir_calculos" style="margin-top: 22px; cursor: pointer" onclick="window.open('{{route('exibir_calculos', ['full' => 1])}}')"></i>
                    <i class="fa fa-cloud-upload-alt fa-inverse fa-lg" id="exportar" style="margin-top: 22px; cursor: pointer"></i>
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
            <div class="modal fade" id="modal_consolidado" role="dialog" tabindex="-1">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"></span></button>
                            <h4 class="modal-title titulo_consolidado_modal"></h4>
                        </div>
                        <div class="modal-body row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label style="display:block">Valor</label>
                                    <input class="form-control valor_consolidado_modal" type="text" />
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer footer_form_movimentacao">
                            <button type="button" class="btn btn-primary salvar">Salvar</button>
                            <button type="button" class="btn btn-primary cancelar" data-dismiss="modal">Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row div_movimentacoes">
                @for ($m=0;$m<=6;$m++)
                    <div class="col-sm-2 col-sm-2-mes">
                        <table class="table table-condensed table-bordered tabela_mes">
                            <thead>
                                <tr>
                                    <th>{{$movimentacoes_mes[$m]['mes']}}</th>
                                    <th class="text-right">
                                        <i class="fas fa-plus-square"></i>
                                        <input type="hidden" class="mes_clicado" value="{{$movimentacoes_mes[$m]['numero_mes']}}">
                                        <input type="hidden" class="ano_clicado" value="{{$movimentacoes_mes[$m]['ano']}}">
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (count($movimentacoes_mes[$m]['movimentacoes']) > 0)
                                    @switch ($m)
                                        @case (0)
                                        @break
                                        @case (1)
                                            @php $total_atual = $movimentacoes_mes[$m]['salario']->valor+$sobra @endphp
                                        @break
                                        @default
                                            @php $total_atual = $movimentacoes_mes[$m]['salario']->valor @endphp
                                        @break
                                    @endswitch
                                    @php
                                        $total_mes = 0;
                                        $renda_mes = 0;
                                        $total_planejado = 0;
                                    @endphp
                                    <tr class="linha_{{$movimentacoes_mes[$m]['salario']->status}} linha_renda">
                                        <input type="hidden" class="id_movimentacao" value="{{$movimentacoes_mes[$m]['salario']->id}}" />
                                        <td class='td_nome_movimentacao' data-toggle="tooltip" data-container="body">salario{{($m == 1) ? ' + sobra' : ''}}</td>
                                        @php $salario = $movimentacoes_mes[$m]['salario']->valor @endphp
                                        @if ($m == 1)
                                            <td class="text-right td_valor">{{$helper->format($salario+$sobra)}}</td>
                                        @else
                                            <td class="text-right td_valor">{{$helper->format($salario)}}</td>
                                        @endif
                                        @php
                                            if ($movimentacoes_mes[$m]['salario']->status != 'pago') {
                                            }
                                        @endphp
                                    </tr>
                                    @for ($i=0;$i<$maximo_movimentacoes;$i++)
                                        @isset($movimentacoes_mes[$m]['movimentacoes'][$i])
                                            <tr class="linha_{{$movimentacoes_mes[$m]['movimentacoes'][$i]->status}} linha_{{$movimentacoes_mes[$m]['movimentacoes'][$i]->tipo}}">
                                                <input type="hidden" class="id_movimentacao" value="{{$movimentacoes_mes[$m]['movimentacoes'][$i]->id}}" />
                                                <td class='td_nome_movimentacao' data-toggle="tooltip" data-container="body" title="{{$movimentacoes_mes[$m]['movimentacoes'][$i]->descricao}}">
                                                    {{$movimentacoes_mes[$m]['movimentacoes'][$i]->nome}}
                                                    @if ($movimentacoes_mes[$m]['movimentacoes'][$i]->id_cartao != '')
                                                        <i class="fa fa-cc {{$modelCartoes::find($movimentacoes_mes[$m]['movimentacoes'][$i]->id_cartao)->sigla}}"></i>
                                                    @endif
                                                    @if ($movimentacoes_mes[$m]['movimentacoes'][$i]->itau)
                                                        <i class="fa fa-info-circle"></i>
                                                    @endif
                                                    @if ($movimentacoes_mes[$m]['movimentacoes'][$i]->tipo == 'terceiros' && !in_array($movimentacoes_mes[$m]['movimentacoes'][$i]->status, ['planejado', 'definido', 'pago']))
                                                        [{{$movimentacoes_mes[$m]['movimentacoes'][$i]->status}}]
                                                    @endif
                                                </td>
                                                <td class="text-right td_valor">{{$helper->format($movimentacoes_mes[$m]['movimentacoes'][$i]->valor)}}</td>
                                            </tr>
                                            @php
                                                if ($movimentacoes_mes[$m]['movimentacoes'][$i]->status != 'pago') {
                                                    if ($movimentacoes_mes[$m]['movimentacoes'][$i]->tipo == 'gasto') {
                                                        $total_mes += $movimentacoes_mes[$m]['movimentacoes'][$i]->valor;
                                                        if ($movimentacoes_mes[$m]['movimentacoes'][$i]->status == 'planejado') {
                                                            $total_planejado += $movimentacoes_mes[$m]['movimentacoes'][$i]->valor;
                                                        }
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
                                    <td>Total</td>
                                    <td class="text-right"><span class="valor_total">{{$helper->format($total_mes-$renda_mes)}}</span></td>
                                </tr>
                                @if ($m > 0)
                                <tr>
                                    <td>Definido</td>
                                    <td class="text-right"><span class="valor_total">{{$helper->format($total_mes-$renda_mes-$total_planejado)}}</span></td>
                                </tr>
                                @endif
                                <tr>
                                    <td>Save</td>
                                    @php
                                        if ($m == 0) {
                                            $sobra = str_replace(",","",$total_atual)-$total_mes+$renda_mes-@$movimentacoes_mes[$m]['save']->valor;    
                                        }
                                        else {
                                            $sobra = str_replace(",","",$total_atual)-($total_mes-$renda_mes);
                                            $save_mes[$m] = $sobra;
                                        }
                                    @endphp
                                    @if ($m == 0)
                                        <td class="text-right td_save">{{$helper->format(@$movimentacoes_mes[$m]['save']->valor)}}</td>
                                    @else
                                        <td class="text-right">{{$helper->format($save_mes[$m])}}</td>
                                    @endif
                                </tr>
                                @if ($m == 0)
                                <tr>
                                    <td>Sobra</td>
                                    <td class="text-right"><span class="valor_sobra">{{$helper->format($sobra)}}</span></td>
                                </tr>
                                @endif
                            </tfoot>
                        </table>
                    </div>
                @endfor
            </div>
            <div class="row div_movimentacoes">
                @for ($t=0;$t<=6;$t++)
                    <div class="col-sm-2 col-sm-2-mes">
                        <table class="table table-condensed table-bordered tabela_mes tabela_terceiros" style='display: none'>
                            <tbody>
                                @if (count($movimentacoes_terceiros[$t]) > 0)
                                    @for ($i=0;$i<$maximo_movimentacoes_terceiros;$i++)
                                        @isset($movimentacoes_terceiros[$t][$i])
                                            <tr class="linha_{{$movimentacoes_terceiros[$t][$i]->status}} linha_{{$movimentacoes_terceiros[$t][$i]->tipo}}">
                                                <input type="hidden" class="id_movimentacao" value="{{$movimentacoes_terceiros[$t][$i]->id}}" />
                                                <td class='td_nome_movimentacao' data-toggle="tooltip" data-container="body">
                                                    {{$movimentacoes_terceiros[$t][$i]->nome}}
                                                    @if ($movimentacoes_terceiros[$t][$i]->id_cartao != '')
                                                        <i class="fa fa-cc {{$modelCartoes::find($movimentacoes_terceiros[$t][$i]->id_cartao)->sigla}}"></i>
                                                    @endif
                                                    [{{$movimentacoes_terceiros[$t][$i]->responsavel}}]
                                                </td>
                                                <td class="text-right td_valor">{{$helper->format($movimentacoes_terceiros[$t][$i]->valor)}}</td>
                                            </tr>
                                        @endisset
                                        @empty($movimentacoes_terceiros[$t][$i])
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
                        </table>
                    </div>
                @endfor
            </div>
            <div class="row div_movimentacoes">
                @for ($s=0;$s<=6;$s++)
                    @php
                        if ($s == 0) {
                            $savings_mes[$s] = $consolidado->where('nome', 'savings')->first()->valor+@$movimentacoes_mes[$s]['save']->valor;
                        }
                        else {
                            $savings_mes[$s] = $savings_mes[$s-1]+$save_mes[$s];
                        }
                    @endphp
                    <div class="col-sm-2 col-sm-2-mes">
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
                                        <td class="text-right">{{$helper->format(@$movimentacoes_mes[$s]['save']->valor)}}</td>
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
