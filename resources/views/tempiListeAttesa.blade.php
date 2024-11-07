@extends('bootstrap-italia::page')


@section(section: "bootstrapitalia_js")
    <script src="{{ asset("js/chart.js") }}"></script>
@endsection


@section('content')

<h2 class="text-center my-4">Obiettivo 1: Riduzione dei tempi delle liste di attesa delle prestazioni sanitarie</h2>


<div class="card shadow-sm border-0">
    <div class="card-header bg-primary text-white">
        <b
            class="h4">{{ __('Numero di accertamenti di morte con criterio neurologico/numero di decessi aziendali per grave neurolesione') }}</b>
        <br />
        <small class="h6"></small>
    </div>
    <div class="card-body">

        <form action="{{ route("tempiListeAttesa") }}" method="GET">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="anno">Anno:</label>
                    <select name="anno" class="form-control">
                        @for ($i = date('Y'); $i >= 2023; $i-- )
                            <option value="{{ $i }}" {{ (isset($dataView['anno']) && $dataView['anno'] == $i) ? "selected" : "" }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-4 mb-4">
                    <label for="mese_inizio">Mese inizio:</label>
                    <select name="mese_inizio" class="form-control">
                        @foreach (config("constants.MESI") as $mese => $value)
                            <option value="{{ $value }}" {{ (isset($dataView['meseInizio']) && $dataView['meseInizio'] == $value) ? "selected" : "" }}>{{ $mese }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-4">
                    <label for="mese_fine">Mese fine:</label>
                    <select name="mese_fine" class="form-control">
                        @foreach (config("constants.MESI") as $mese => $value)
                            <option value="{{ $value }}" {{ (isset($dataView['meseFine']) && $dataView['meseFine'] == $value) ? "selected" : "" }}>{{ $mese }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 mb-1 d-flex align-items-end">
                    <button id="searchButton" class="btn btn-primary" type="submit">Cerca</button>
                </div>
            </div>
        </form>

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
                            <th>Denominatore (flusso M + flusso C)</th>
                            <th>% Primo Accesso</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $dataView['numeratore'] }}</td>
                            <td>{{ $dataView['denominatore'] }}</td>
                            <td>{{ $dataView['percentuale'] }} %</td>
                        </tr>
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