<!DOCTYPE html>
<html>

<head>
    <title>Autocerticazione</title>
    <style>
        .right {
            position: absolute;
            right: 20px;
            padding: 10px;
        }
    </style>
</head>

<body>

    @if (config('bootstrap-italia.logo'))
        @if (config('bootstrap-italia.logo.type') === 'icon')
            <svg class="icon">
                <use
                    xlink:href="{{ asset('vendor/bootstrap-italia/dist/svg/sprite.svg#it-') }}{{ config('bootstrap-italia.logo.icon') }}">
                </use>
            </svg>
        @else
            <img alt="logo" class="icon" src="{{ config('bootstrap-italia.logo.url') }}">
        @endif
    @endif

    <h1>Obiettivo 5</h1>

    @foreach ($tableData as $dati)
        <p>Anno: <strong{{ $dati->year }}<strong></strong> </p>

        <p>Totale MMG: <strong>{{ $dati->mmg_totale }}</strong></p>
        <p>MMG Coinvolti:<strong>{{ $dati->mmg_coinvolti }}</strong></>
        <p>Struttura:<strong>{{$dati->nome_struttura}}</strong></p>

    @endforeach
    <div class="right">
        <i>Firma elettronica<br>{{ Auth::user()->name }}</i>
    </div>
</body>

</html>