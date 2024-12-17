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
    
    <h1>Obiettivo 1</h1>

    <p>Anno selezionato: {{ $anno }}</p>
    <p>Struttura selezionata: {{ $struttura->name }}</p>

    <h3>Indicatore 1.2 - Favorire la presa in carico dei pazienti affetti da patologie cronico-degenerative e
        oncologiche (D.L. 73/2024)</h3>
    <p>Numero di agende dedicate ai PTDA aziendali: {{ $numero_agende }}</p>
    <p>Num prest. di controllo prescritte dallo specialista ambulatoriale (Anno {{$anno}}):
        {{ $prestazioni_specialista_riferimento }}</p>
    <p>Num prest. di controllo prescritte dallo specialista ambulatoriale (Anno {{$anno - 1}}):
        {{ $prestazioni_specialista_precedente }}</p>
    <p></p>
    <p>Num prest. di controllo prescritte da MMG/PLS (Anno {{$anno}}): {{ $prestazioni_MMG_riferimento }}</p>
    <p>Num prest. di controllo prescritte da MMG/PLS (Anno {{$anno - 1}}): {{ $prestazioni_MMG_precedente }}</p>

    <p></p>
    <p></p>
    <p>Data: {{ $data }}</p>
    <div class="right">
        <i>Firma elettronica<br>{{ Auth::user()->name }}</i>
    </div>
</body>

</html>