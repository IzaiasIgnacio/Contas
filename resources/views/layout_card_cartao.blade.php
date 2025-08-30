<div class="col-xl-6 col-lg-6 mb-4">
    <div class="card text-white bg-dark">
        <div class="card-header {{$cartao['cartao']->sigla}}">
            <h5 class="card-title mb-0">
                <img style="max-height: 15px;" src="http://localhost/contas/public/imagens/{{$cartao['cartao']->nome}}.png">
                {{$cartao['cartao']->rotulo}} {{substr($cartao['cartao']->numero, -4)}} L: {{$cartao['limite']}} F: {{$cartao['fechamento']}} V: {{$cartao['vencimento']}}
            </h5>
        </div>
        <div class="card-body">
            <div class="row ">
            @foreach ($cartao['meses_cartao'] as $mes)
                    <div class="col-xl-4 col-lg-4">
                        <div class="card {{$cartao['cartao']->sigla}}">
                            <div class="card-statistic-3 p-2 resumo_mes">
                                <div class="mb-2 text-start">
                                    <h5 class="card-title mb-0">{{$mes['nome']}}</h5>
                                </div>
                                <div class="row d-flex">
                                    <div class="col-12 text-end">
                                        <div class="row">
                                            <div class="col-6 text-start">Gastos</div>
                                            <div class="col-6 text-end">{{$mes['gastos']}}</div>
                                        </div>
                                    </div>
                                    <div class="col-12 text-end mb-1">
                                        <div class="row">
                                            <div class="col-6 text-start">Limite</div>
                                            <div class="col-6 text-end">{{$mes['limite_atual']}}</div>
                                        </div>
                                    </div>
                                </div> 
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>