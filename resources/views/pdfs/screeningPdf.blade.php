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

    <img src="{{'data:image/png;base64,'.base64_encode(string: file_get_contents( public_path('/logo-regione-sicilia.png')))}}">

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