<table class="table table-sm table-bordered tabela_mes">
    <thead>
        <tr>
            <th>{{$mes['nome']}}</th>
            <th class="text-end">
                <i class="fas fa-plus-square"></i>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class='' data-toggle="tooltip" data-container="body">salario</td>
            <td class="text-end">9590</td>
        </tr>
        @foreach ($mes['movimentacoes'] as $movimentacao)
        <tr>
            <td class='' data-toggle="tooltip" data-container="body">
                {{$movimentacao['nome']}}
            </td>
            <td class="text-end">{{$helper->format($movimentacao['valor'])}}</td>
        </tr>
        @endforeach
        @for ($i=0;$i<=$maximo_movimentacoes-count($mes['movimentacoes']);$i++)
        <tr>
            <td class='' data-toggle="tooltip" data-container="body"></td>
            <td class="text-end">{{$i}}</td>
        </tr>
        @endfor
    </tbody>
    <tfoot>
        <tr>
            <td>Total</td>
            <td class="text-end"><span class="valor_total">{{$maximo_movimentacoes}}||{{count($mes['movimentacoes'])}}||{{$maximo_movimentacoes-count($mes['movimentacoes'])}}||{{$helper->format($mes['total'])}}</span></td>
        </tr>
        <tr>
            <td>Gastos</td>
            <td class="text-end"><span class="valor_total">{{$helper->format($mes['gastos'])}}</span></td>
        </tr>
        <tr>
            <td>Renda</td>
            <td class="text-end"><span class="valor_total">{{$helper->format($mes['renda'])}}</span></td>
        </tr>
        <tr>
            <td>Saldo</td>
            <td class="text-end"><span class="valor_total">{{$helper->format($mes['saldo'])}}</span></td>
        </tr>
        <tr>
            <td>Saldo Total</td>
            <td class="text-end"><span class="valor_sobra">{{$helper->format($mes['saldo_total'])}}</span></td>
        </tr>
        </tr>
        <tr>
            <td>Margem</td>
            <td class="text-end"><span class="valor_sobra">{{$helper->format($mes['margem'])}}</span></td>
        </tr>
    </tfoot>
</table>