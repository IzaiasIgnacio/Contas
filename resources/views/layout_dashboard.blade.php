<div class="row">
    @foreach ($meses as $mes)
        @include('layout_card_mes', ['mes' => $mes])
    @endforeach
</div>

<div class="row row_cartoes">
    @foreach ($cartoes as $cartao)
        @include('layout_card_cartao', ['cartao' => $cartao])
    @endforeach
</div>