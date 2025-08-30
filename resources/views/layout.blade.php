<!doctype html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Contas</title>
        <link rel="stylesheet" href="{{URL::asset('public/css/bootstrap5.3/bootstrap.min.css')}}" />
        <link rel="stylesheet" href="{{URL::asset('public/css/sidebars.css')}}" />
        <link rel="stylesheet" href="{{URL::asset('public/css/cards.css')}}" />
        <link rel="stylesheet" href="{{URL::asset('public/css/tabelas.css')}}" />
        <link rel="stylesheet" href="{{URL::asset('public/css/layout.css')}}" />
        <link rel="stylesheet" href="{{URL::asset('public/css/fontawesome/fontawesome-4.7.0.css')}}" />
        <link rel="stylesheet" href="{{URL::asset('public/css/fontawesome/fontawesome-all.css')}}" />
        <script src="{{URL::asset('public/js/bootstrap5.3/bootstrap.min.js')}}"></script>
        <script src="{{URL::asset('public/js/sidebars.js')}}"></script>
    </head>
    <body>
        <main class="d-flex flex-nowrap">
            <div class="d-flex flex-column flex-shrink-0 sidebar_menu" style="width: 4.5rem;height: 100vw">
                <ul class="nav nav-pills nav-flush flex-column mb-auto text-start">
                    <li class="li_topo">
                        <i class="fa fa-user fa-lg icone_sidebar"></i>
                    </li>
                    <li class="li_sidebar {{($pagina == 'layout_dashboard') ? 'ativo' : ''}}">
                        <a href="/contas/dashboard" >
                            <i class="fa fa-user fa-lg icone_sidebar"></i>
                        </a>
                    </li>
                    <li class="li_sidebar {{($pagina == 'layout_contas') ? 'ativo' : ''}}">
                        <a href="/contas/tabela" >
                            <i class="fa fa-user fa-lg icone_sidebar"></i>
                        </a>
                    </li>
                    <li class="li_sidebar {{($pagina == 'layout_calculos') ? 'ativo' : ''}}">
                        <a href="/contas/calculos" target="_blank" >
                            <i class="fa fa-user fa-lg icone_sidebar"></i>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="container container_layout">
                @include($pagina)
            </div>
        </main>
    </body>
</html>