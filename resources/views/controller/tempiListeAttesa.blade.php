@extends('bootstrap-italia::page')


@section("bootstrapitalia_js")
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3"></script>
@endsection


@section('content')

<h2 class="text-center my-4">Obiettivo 1: Riduzione dei tempi delle liste di attesa delle prestazioni sanitarie</h2>


<div class="card shadow-sm border-0">
    <div class="card-header bg-primary text-white">
        <b
            class="h4">{{ __('Numero di accertamenti di morte con criterio neurologico/numero di decessi aziendali per grave neurolesione') }}</b>
        <br />
        <small class="h6">{{ __('aggiungere descrizione') }}</small>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 text-center">
                <div style="width: 100%; max-width: 400px; margin: auto;">
                    <x-chartjs-component :chart=" $dataView['tempiListeAttesa']" />
                </div>
            </div>

            <div class="col-md-6">
                <h5 class="text-center">Tabella</h5>
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Numeratore</th>
                            <th>Denominatore (M + C)</th>
                            <th>% Primo Accesso</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dataView['flussoC'] as $index => $item) 
                            <tr>
                                <td>{{ 99609 }}</td>
                                <td>{{ $dataView['denominatoreTotale'] }}</td>
                                <td>{{ number_format($dataView['percentuali'][$index], 2) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <div class="legend p-3 border rounded">
        <strong>Valori di riferimento</strong><br>
        <table class="formula-table">
            <tr class="numeratore">
                <td>Numero prestazioni ambulatoriali di primo accesso pubbliche e private accreditate<sup>^</sup></td>
            </tr>
            <tr class="denominatore">
                <td>Numero di prestazioni ambulatoriali erogate<sup>^^</sup></td>
            </tr>
        </table>
        <p>
            <sup>^</sup> (fonte dati: esclusivamente CUP di ASP e Aziende Ospedaliere; i dati delle prestazioni private
            accreditate devono obbligatoriamente essere estratti esclusivamente dai CUP delle ASP)</br>
            <sup>^^</sup> (fonte dati: flusso C e flusso M)
        </p><br>
        <strong>L'obiettivo è raggiunto al 100% se il rapporto è pari a 1 (100%)</strong>
    </div>


</div>






@endsection