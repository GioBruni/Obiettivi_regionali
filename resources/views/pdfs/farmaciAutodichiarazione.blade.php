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
    <h1>Obiettivo 9.2</h1>
    <h3>Ottimizzazione della gestione del I ciclo di terapia a pazienti dimessi sia in DH che in ricovero ordinario.</h3>

    <h3>Elenco dei valori inseriti</h3>
    <p>Struttura selezionata: {{ $struttura->name }}</p>
    <p>Numeratore: {{ $numeratore }}</p>
    <p>Denominatore: {{ $denominatore }}</p>
    <p>Rapporto calcolato tra numeratore e denominatore: {{ $rapporto }} %</p>

    <p></p>
    <p></p>
    <p>{{ $data }}</p>
    <div class="right">
        <i>Firma elettronica<br>{{ Auth::user()->name }}</i>
    </div>
</body>
</html>
