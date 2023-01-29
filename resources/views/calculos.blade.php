<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Contas</title>
        <link rel="shortcut icon" href="{{URL::asset('public/favicon.ico')}}">
        <link rel="stylesheet" href="{{URL::asset('public/css/bootstrap/bootstrap.min.css')}}" />
        <link rel="stylesheet" href="{{URL::asset('public/css/calculos.css')}}" />
        <script src="{{URL::asset('public/js/jquery.min.js')}}"></script>
        <script src="{{URL::asset('public/js/bootstrap.min.js')}}"></script>
    </head>
    <body>
        <nav class="navbar navbar-inverse navbar-fixed-top">
            <div class="container menu_container">
                <ul class="nav navbar-brand">
                    <li>Contas</li>
                </ul>
            </div>
        </nav>
        <div class="container body-content">
            <div class="col-sm-2">
                <div class="row">
                    <table class="table table-condensed table-bordered table-striped">
                        <tbody>
                            @foreach ($gastos as $responsavel => $movimentacoes)
                                @if (in_array($responsavel, ['chah', 'cristiane', 'tio_anisio'])) @continue; @endif
                                <tr>
                                    <td colspan="2" class='td_responsavel'>{{$responsaveis[$responsavel]}}</td>
                                </tr>
                                @php $total_responsavel = 0; @endphp
                                @foreach ($movimentacoes as $mov)
                                    <tr>
                                        <td>{{$mov->nome}}</td>
                                        <td>{{$helper->format($mov->valor)}}</td>
                                    </tr>
                                    @php
                                        if ($mov->nome != 'Atrasado' && $mov->nome != 'pago' && !strstr($mov->nome, 'pago dezembro')) {
                                            $total_responsavel += $mov->valor;
                                        }
                                    @endphp
                                @endforeach
                                <tr class='tr_total_responsavel'>
                                    <td>Total {{$responsaveis[$responsavel]}}</td>
                                    <td>{{$helper->format($total_responsavel)}}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <table class="table table-condensed table-bordered table-striped">
                        <tbody>
                            <tr>
                                <td>Pago</td>
                                <td>{{$helper->format($pago)}}</td>
                            </tr>
                            <tr>
                                <td>Total - Pago</td>
                                <td>{{$helper->format($total - $pago)}}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            @if (Request::get('full'))
            @isset($gastos['cristiane'])
            <div class="col-sm-2">
                <div class="row">
                    <table class="table table-condensed table-bordered table-striped">
                        <tbody>
                            <tr>
                                <td colspan="2" class='td_responsavel'>Cristiane</td>
                            </tr>
                            @foreach ($gastos['cristiane'] as $mov)
                            <tr>
                                    <td>{{$mov->nome}}</td>
                                    <td>{{$helper->format($mov->valor)}}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <table class="table table-condensed table-bordered table-striped">
                        <tbody>
                            <tr>
                                <td>Total</td>
                                <td>{{$helper->format($total_cristiane)}}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            @endisset
            @isset($gastos['chah'])
            <div class="col-sm-2">
                <div class="row">
                    <table class="table table-condensed table-bordered table-striped">
                        <tbody>
                            <tr>
                                <td colspan="2" class='td_responsavel'>Chayane</td>
                            </tr>
                            @foreach ($gastos['chah'] as $mov)
                                <tr>
                                    <td>{{$mov->nome}}</td>
                                    <td>{{$helper->format($mov->valor)}}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <table class="table table-condensed table-bordered table-striped">
                        <tbody>
                            <tr>
                                <td>Total</td>
                                <td>{{$helper->format($total_chah+$antigo_chah)}}</td>
                            </tr>
                            <tr>
                                <td>Total do mês</td>
                                <td>{{$helper->format($total_chah)}}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            @endisset
            @isset($gastos['tio_anisio'])
            <div class="col-sm-2">
                <div class="row">
                    <table class="table table-condensed table-bordered table-striped">
                        <tbody>
                            <tr>
                                <td colspan="2" class='td_responsavel'>Tio Anisio</td>
                            </tr>
                            @foreach ($gastos['tio_anisio'] as $mov)
                                <tr>
                                    <td>{{$mov->nome}}</td>
                                    <td>{{$helper->format($mov->valor)}}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <table class="table table-condensed table-bordered table-striped">
                        <tbody>
                            <tr>
                                <td>Total</td>
                                <td>{{$helper->format($total_tio_anisio)}}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            @endisset
            <div class="col-sm-2">
                <div class="row">
                    <table class="table table-condensed table-bordered table-striped">
                        <tbody>
                            <tr>
                                <td colspan="2" class='td_responsavel'>Itaú</td>
                            </tr>
                            @foreach ($itau as $i)
                                <tr @php if ($i->tipo == 'renda') { echo "class='tr_renda_itau'"; } @endphp>
                                    <td>{{$i->nome}}</td>
                                    <td>{{$helper->format($i->valor)}}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <table class="table table-condensed table-bordered table-striped">
                        <tbody>
                            <tr>
                                <td>Total</td>
                                <td>{{$helper->format($total_itau)}}</td>
                            </tr>
                            <tr>
                                <td>Sobra</td>
                                <td>{{$helper->format($valor_itau - $total_itau)}}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-sm-2">
                <div class="row">
                    <table class="table table-condensed table-bordered table-striped">
                        <tbody>
                            <tr>
                                <td colspan="2" class='td_responsavel'>Nubank</td>
                            </tr>
                            @foreach ($nb as $n)
                                <tr @php if ($n->tipo == 'renda') { echo "class='tr_renda_nb'"; } @endphp>
                                    <td>{{$n->nome}}</td>
                                    <td>{{$helper->format($n->valor)}}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <table class="table table-condensed table-bordered table-striped">
                        <tbody>
                            <tr>
                                <td>Total</td>
                                <td>{{$helper->format($total_nb)}}</td>
                            </tr>
                            <tr>
                                <td>Sobra</td>
                                <td>{{$helper->format($valor_nb - $total_nb)}}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-sm-2">
                <div class="row">
                    <table class="table table-condensed table-bordered table-striped">
                        <tbody>
                            <tr>
                                <td colspan="2" class='td_responsavel'>Iti</td>
                            </tr>
                            @foreach ($iti as $i)
                                <tr @php if ($i->tipo == 'renda') { echo "class='tr_renda_iti'"; } @endphp>
                                    <td>{{$i->nome}}</td>
                                    <td>{{$helper->format($i->valor)}}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <table class="table table-condensed table-bordered table-striped">
                        <tbody>
                            <tr>
                                <td>Total</td>
                                <td>{{$helper->format($total_iti)}}</td>
                            </tr>
                            <tr>
                                <td>Sobra</td>
                                <td>{{$helper->format($valor_iti - $total_iti)}}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </body>
</html>
