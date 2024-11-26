@extends('bootstrap-italia::page')


@section("bootstrapitalia_js")
<script src="{{ asset(path: 'js/chart.js') }}" rel="text/javascript"></script>
@endsection


@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white text-center">
                    <h4>
                        <i class="fas fa-heartbeat"></i>
                        {{ __('Obiettivo 2: esiti') }}
                    </h4>
                    <small>{{ __('Interventi effettuati entro 0-2 giorni dal ricovero / numero totale di casi di frattura femore su pazienti over 65') }}</small>
                </div>
              
                <div class="card-body">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-primary text-white">
                            <b
                                class="h4">{{ __('Tempestività interventi a seguito di frattura femore su pazienti over 65') }}</b>
                            <br />
                        </div>
                        <div class="row justify-content-center mt-4">
                            <div class="col-md-6 text-center">
                                <div style="width: 100%; max-width: 300px; margin: auto;">
                                    <x-chartjs-component :chart="$dataView['chartFratturaFemore']" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Totale MMG</th>
                                            <th>MMG Coinvolti</th>
                                            <th>Percentuale (%)</th>
                                            <th>Struttura</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <tr>
                                            <td>{{ 20}}</td>
                                            <td>{{2 }}</td>
                                            <td>{{33}}</td>
                                            <td>{{5}}</td>
                                        </tr>

                                    </tbody>
                                </table>
                                <div class="legend p-3 border rounded mt-3">
                                    punteggio
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="legend p-3 border rounded">
                            <strong>Scala valori di riferimento (Punteggio massimo 4)</strong><br>
                            <span>Il raggiungimento dell'obiettivo &egrave; riconosciuto a partire dal
                                valore minimo del 60% (DM Salute 70/2025) definibile come valore zero della
                                scala di misura.</span><br>
                            <span>Se il valore dell'indicatore &egrave; minore o uguale al valore di
                                partenza (60%), l'obiettivo è considerato non raggiunto.</span><br />
                            <span>Se il valore è compreso tra il valore di partenza e il valore obiettivo,
                                il grado di raggiungimento è riconosciuto applicando una funzione
                                lineare.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection