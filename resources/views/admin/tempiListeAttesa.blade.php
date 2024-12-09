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
                        1.1 - Numero di accertamenti di morte con criterio neurologico/numero di decessi aziendali per grave neurolesione 
                    </div>

                    <div class="card-body">
                        <form action="{{ route("admin.tempiListeAttesa") }}" method="GET">
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
                            <div class="col-md-12 text-center">
                                <div style="width: 100%; max-width: 100%; margin: auto;">
                                    <x-chartjs-component :chart=" $dataView['tempiListeAttesa']" />
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="text-center">Tabella</h5>
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Struttura</th>
                                            <th>Prest. amb. di I accesso *</th>
                                            <th>Prest. amb. erogate *</th>
                                            <th>% Primo Accesso</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($dataView['dati'] as $dati)
                                        <tr>
                                            <td>{{ $dati['name'] }}</td>
                                            <td class="text-white text-center" style="background-color:{{$dati['backgroundPerc']}};">{{ $dati['numeratore'] }}</td>
                                            <td class="text-white text-center" style="background-color:{{$dati['backgroundPerc']}};">{{ $dati['denominatore'] }}</td>
                                            <td class="text-white text-center" style="background-color:{{$dati['backgroundPerc']}};">{{ $dati['percentuale'] }} %</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <caption>L'obiettivo è raggiunto al 100% se il rapporto è pari a 1 (100%)<br /><div class="it-list-wrapper"><ul class="it-list">
                                        <li>* esclusivamente CUP di ASP e Aziende Ospedaliere; i dati delle prestazioni private accreditate devono obbligatoriamente essere estratti esclusivamente dai CUP delle ASP;</li>
                                        <li>** flusso C e flusso M.</li>
                                    </ul></div></caption>
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
                        <strong></strong>
                    </div>
                </div>

                <div class="box mt-4">
                    <div class="card-header bg-primary text-white mb-3 mt-2">
                        1.2 - Favorire la presa in carico dei pazienti affetti da patologie cronico-degenerative e oncologiche (D.L. 73/2024) 
                    </div>

                    <div class="card-body">
                        <h5 class="text-center">Ind. 1.2 - Numero di agende dedicate ai PTDA aziendali</h5>
                        <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Struttura</th>
                                <th>Agende *</th>
                                <th colspan="3">Prest. da specialista amb. **</th>
                                <th colspan="3">Prest. da MMG/PLS ***</th>
                            </tr>
                            <tr>
                                <th></th>
                                <th></th>
                                <th>2023</th>
                                <th>2024</th>
                                <th>Perc. incr.</th>
                                <th>2023</th>
                                <th>2024</th>
                                <th>Perc. incr.</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($dataView['indicatori2'] as $dato)
                            <tr>
                                <td>{{ $dato['name'] }}</td>
                                <td class="text-white text-center" style="background-color:{{$dato['backgroundAgende']}};">{{ $dato['numero_agende'] }}</td>
                                <td class="text-white text-center" style="background-color:{{$dato['backgroundSpecialista']}};">{{ $dato['prestazioni_specialista_precedente'] }}</td>
                                <td class="text-white text-center" style="background-color:{{$dato['backgroundSpecialista']}};">{{ $dato['prestazioni_specialista_riferimento'] }}</td>
                                <td class="text-white text-center" style="background-color:{{$dato['backgroundSpecialista']}};">{{ $dato['percentualeSpecialista'] }} %</td>
                                <td class="text-white text-center" style="background-color:{{$dato['backgroundMMG']}};">{{ $dato['prestazioni_MMG_precedente'] }}</td>
                                <td class="text-white text-center" style="background-color:{{$dato['backgroundMMG']}};">{{ $dato['prestazioni_MMG_riferimento'] }}</td>
                                <td class="text-white text-center" style="background-color:{{$dato['backgroundMMG']}};">{{ $dato['percentualeMMG'] }} %</td>
                            </tr>                    
                        @endforeach
                        </tbody>
                        <caption><div class="it-list-wrapper"><ul class="it-list">
                            <li>* Indicatore 2.1: obiettivo raggiunto al 100% se il num di agende dedicate ai PTDA &egrave; > n. 10;</li>
                            <li>** Indicatore 2.2: obiettivo raggiunto al 100% se il num di prest. di controllo prescritte direttamente dallo specialista &egrave; > al 10% rispetto al periodo di riferimento dell'anno precedente;</li>
                            <li>*** Indicatore 2.3: obiettivo raggiunto al 100% se il num di prest. di controllo prescritte da MMG/PLS &egrave; < 20% rispetto al periodo di riferimento dell'anno precedente.</li>
                        </ul></div></caption>
                        </table>
                    </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>

@endsection