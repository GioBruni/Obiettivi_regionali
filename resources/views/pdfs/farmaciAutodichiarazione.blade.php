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

    <h1>Obiettivo 9</h1>

    <p>Anno selezionato: {{ $anno }}</p>
    <p>Struttura selezionata: {{ $struttura->name }}</p>

    <h3>Indicatore 9.2 - Ottimizzazione della gestione del I ciclo di terapia a pazienti dimessi sia in DH che in ricovero ordinario.</h3>
    <p>Numeratore: {{ $numeratore }}</p>
    <p>Denominatore: {{ $denominatore }}</p>
    <p>Rapporto calcolato tra numeratore e denominatore: {{ $rapporto }} %</p>

    <p></p>
    <p></p>
    <p>Data: {{ $data }}</p>
    <div class="right">
        <i>Firma elettronica<br>{{ Auth::user()->name }}</i>
    </div>
</body>
</html>
