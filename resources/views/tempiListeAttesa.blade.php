@extends('bootstrap-italia::page')


@section(section: "bootstrapitalia_js")
<script src="{{ asset("js/chart.js") }}"></script>
@endsection


@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white text-center">
                    <h4>
                        <i class="{{ $dataView['icona'] }}"></i>
                        {{ $dataView['titolo'] }}
                    </h4>
                    <small>{{ $dataView['tooltip'] }}</small>
                </div>
                @if (session(key: 'status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="box mt-4">
                    <div class="card-header bg-primary text-white mb-3 mt-2">
                        1.1 - Numero di accertamenti di morte con criterio neurologico/numero di decessi aziendali per
                        grave neurolesione
                    </div>

                    <div class="card-body">
                        <form action="{{ route("tempiListeAttesa") }}" method="GET">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label for="anno">Anno:</label>
                                    <select name="anno" class="form-control">
                                        @for ($i = date('Y'); $i >= 2023; $i--)
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
                                <div class="legend p-3 border rounded mt-3">
                                    <strong>Punteggio: {{$dataView['punteggio']}}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="legend p-3 border rounded">
                        <strong>Valori di riferimento</strong><br>
                        <table class="formula-table">
                            <tr class="numeratore">
                                <td>Numero prestazioni ambulatoriali di primo accesso pubbliche e private
                                    accreditate<sup>^</sup></td>
                            </tr>
                            <tr class="denominatore">
                                <td>Numero di prestazioni ambulatoriali erogate<sup>^^</sup></td>
                            </tr>
                        </table>
                        <p>
                            <sup>^</sup> (fonte dati: esclusivamente CUP di ASP e Aziende Ospedaliere; i dati delle
                            prestazioni private
                            accreditate devono obbligatoriamente essere estratti esclusivamente dai CUP delle ASP)</br>
                            <sup>^^</sup> (fonte dati: flusso C e flusso M)
                        </p><br>
                        <strong>L'obiettivo è raggiunto al 100% se il rapporto è pari a 1 (100%)</strong>
                    </div>
                </div>

                <div class="box mt-4">
                    <div class="card-header bg-primary text-white mb-3 mt-2">
                        1.2 - Favorire la presa in carico dei pazienti affetti da patologie cronico-degenerative e
                        oncologiche (D.L. 73/2024)
                    </div>

                    <div class="card-body">
                        @foreach ($dataView["categorie"] as $categoria)
                            <?php    $trovata = -1; ?>
                            <div class="card shadow-sm border-0 mt-2">
                                <div class="card-header bg-primary text-white">
                                    <strong>{{ $categoria->category }}</strong>
                                </div>
                                <div class="card-body">
                                    @foreach($dataView['filesCaricati'] as $file)
                                        @if ($file->target_category_id == $categoria->id)
                                            <?php            $trovata = 1; ?>
                                            @if($file->validator_user_id === null)
                                                <div id="message5"
                                                    class="message bg-light p-3 rounded border border-primary text-center w-100"
                                                    style="color: orange;">
                                                    <strong>File caricato il:
                                                        {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $file->created_at)->format('d/m/Y H:i') }}
                                                        in attesa di approvazione.</strong>
                                                </div>
                                            @else
                                                <div id="message5"
                                                    class="message bg-light p-3 rounded border border-primary text-center w-100"
                                                    style="color:{{ $file->approved === 1 ? "green" : "red"}};">
                                                    <strong>Il file caricato il:
                                                        {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $file->created_at)->format('d/m/Y H:i') }}
                                                        {{ $file->approved === 1 ? "" : "non " }}&egrave; stato approvato -> Obiettivo
                                                        {{ $file->approved === 1 ? "" : "non " }}raggiunto!</strong>
                                                </div>
                                            @endif
                                        @endif
                                    @endforeach
                                    @if ($trovata == -1)
                                        <div id="message5"
                                            class="message bg-light p-3 rounded border border-primary text-center w-100"
                                            style="color:red;">
                                            <strong>Il file non &egrave; ancora stato caricato -> Obiettivo non
                                                raggiunto!</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection