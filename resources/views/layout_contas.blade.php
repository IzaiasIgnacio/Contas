<div class="row div_movimentacoes">
    @foreach ($meses as $mes)
    <div class="col-xl col-lg">
        @include('layout_tabela_mes')
    </div>
    @endforeach
</div>