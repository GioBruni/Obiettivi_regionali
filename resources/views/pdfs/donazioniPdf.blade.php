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