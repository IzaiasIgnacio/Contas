<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Contas</title>
        <link rel="stylesheet" href="{{URL::asset('public/css/bootstrap/bootstrap.min.css')}}" />
        <link rel="stylesheet" href="{{URL::asset('public/css/app.css')}}" />
        <link rel="stylesheet" href="{{URL::asset('public/css/terceiros.css')}}" />
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
            <div class="col-md-2">
                <div class="row">
                    <table class="table table-condensed table-bordered table-stripped">
                        <tr>
                            <td colspan="2" class='td_responsavel'>Responsavel</td>
                        </tr>
                        <tr>
                            <td>Nome</td>
                            <td>Valor</td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="col-md-2">
                <div class="row">
                    <table class="table table-condensed table-bordered table-stripped">
                        <tr>
                            <td>Nome</td>
                            <td>Valor</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </body>
</html>
