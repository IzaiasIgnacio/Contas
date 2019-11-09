<form id="form_movimentacao">
    <input type="hidden" name="Id" value="@Model.Id">
    <div class="col-md-6">
        <div class="form-group">
            <label style="display:block">Nome</label>
            <input class="form-control" type="text" name='nome' id='nome' />
        </div>
        <div class="form-group">
            <label style="display:block">Data</label>
            <input class="form-control" type="text" name='data' id='data' value="{{date('d/m/Y')}}" />
        </div>
        <div class="form-group">
            <label style="display:block">Tipo</label>
            <select name='tipo' id='tipo' class="form-control">
                @foreach ($tipos as $tipo)
                    <option value="{{$tipo}}">{{$tipo}}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label style="display:block">Valor</label>
            <input class="form-control" type="text" name='valor' id='valor' />
        </div>
        <div class="form-group">
            <label style="display:block">Status</label>
            <select name='status' id='status' class="form-control">
                @foreach ($lista_status as $status)
                    <option value="{{$status}}">{{$status}}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label style="display:block">Cartão</label>
            <select name='cartao' id='cartao' class="form-control">
                <option value="">Nenhum</option>
                @foreach ($cartoes as $cartao)
                    <option value="{{$cartao->id}}">{{$cartao->nome}}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label style="display:block">Parcelas</label>
            <input class="form-control" type="text" name='parcelas' id='parcelas' />
        </div>
    </div>
</form>