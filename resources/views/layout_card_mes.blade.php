<div class="col-xl-2 col-lg-2">
    <div class="card l-bg-blue-dark">
        <div class="card-statistic-3 p-2 resumo_mes">
            <div class="mb-2 text-start">
                <h5 class="card-title mb-0">{{$mes['nome']}}</h5>
            </div>
            <div class="row d-flex" style="font-weight: bold;">
                <div class="col-12 text-end mb-1">
                    <div class="row" style="font-weight: bold;">
                        <div class="col-8 text-start">Total</div>
                        <div class="col-4 text-end">{{$mes['total_atual']}}</div>
                    </div>
                </div>
                <div class="col-12 text-end">
                    <div class="row" style="color: red;">
                        <div class="col-8 text-start">Gastos</div>
                        <div class="col-4 text-end">{{$mes['gastos']}}</div>
                    </div>
                </div>
                <div class="col-12 text-end mb-1">
                    <div class="row" style="color: #03993c;">
                        <div class="col-8 text-start">Renda</div>
                        <div class="col-4 text-end">{{$mes['renda']}}</div>
                    </div>
                </div>
                <div class="col-12 text-end">
                    <div class="row" style="font-weight: bolder;">
                        <div class="col-8 text-start">Saldo</div>
                        <div class="col-4 text-end">{{$mes['saldo']}}</div>
                    </div>
                </div>
                <div class="col-12 text-end">
                    <div class="row">
                        <div class="col-8 text-start">Diferença</div>
                        <div class="col-4 text-end">{{$mes['diferenca']}}</div>
                    </div>
                </div>
                <div class="col-12 text-end">
                    <div class="row">
                        <div class="col-8 text-start">Margem</div>
                        <div class="col-4 text-end">{{$mes['margem']}}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>