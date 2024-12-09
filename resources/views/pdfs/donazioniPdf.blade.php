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

    <h1>Obiettivo 6</h1>

    @foreach($target6_data as $dati)
        <p><strong>Anno:</strong> {{ $dati->anno }} </p>
        <p>Totale accertamenti: {{ $dati->totale_accertamenti }} </p>
        <p>Numero opposti: {{ $dati->numero_opposti }}</p>
        <p>Totale cornee: {{$dati->totale_cornee}}</p>
    @endforeach

    
    <div class="right">
        <i>Firma elettronica<br>{{ Auth::user()->name }}</i>
    </div>
</body>

</html>