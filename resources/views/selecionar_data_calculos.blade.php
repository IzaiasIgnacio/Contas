<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Contas</title>
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
            <form id="form_data" method='POST' action="{{route('exibir_calculos')}}">
                <input type='hidden' name='full' id='full' value='{{(Request::get('full'))}}' />
                <div class="col-sm-4">
                    <div class="form-group">
                        <label style="display:block">Mês</label>
                        <select name='mes' id='mes' class="form-control">
                            <option value="1"{{(date('m') == 1) ? "selected": ""}}>Janeiro</option>
                            <option value="2"{{(date('m') == 2) ? "selected": ""}}>Fevereiro</option>
                            <option value="3"{{(date('m') == 3) ? "selected": ""}}>Março</option>
                            <option value="4"{{(date('m') == 4) ? "selected": ""}}>Abril</option>
                            <option value="5"{{(date('m') == 5) ? "selected": ""}}>Maio</option>
                            <option value="6"{{(date('m') == 6) ? "selected": ""}}>Junho</option>
                            <option value="7"{{(date('m') == 7) ? "selected": ""}}>Julho</option>
                            <option value="8"{{(date('m') == 8) ? "selected": ""}}>Agosto</option>
                            <option value="9"{{(date('m') == 9) ? "selected": ""}}>Setembro</option>
                            <option value="10"{{(date('m') == 10) ? "selected": ""}}>Outubro</option>
                            <option value="11"{{(date('m') == 11) ? "selected": ""}}>Novembro</option>
                            <option value="12"{{(date('m') == 12) ? "selected": ""}}>Dezembro</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="display:block">Ano</label>
                        <input class="form-control" type="text" name='ano' id='ano' value="{{date('Y')}}" />
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Exibir</button>
                    </div>
                </div>
            </form>
        </div>
    </body>
</html>