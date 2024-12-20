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
                        <i class="{{ $dataView['icona'] }}"></i>
                        {{ $dataView['titolo'] }}
                    </h4>
                    <small>{{ $dataView['tooltip'] }}</small>
                </div>

                <form action="{{ route('admin.esiti') }}" method="GET" enctype="multipart/form-data">
                    <div class="p-3">
                        <div class="row">
                            <!-- Selezione dell'anno -->
                            <div class="col-md-4 mb-3">
                                <label for="annoSelezionato" class="form-label">Anno:</label>
                                <div class="d-flex">
                                <select id="year" class="form-control" name="year" id="year">
                                            @foreach ( $dataView['anni'] as $year )
                                                <option value="{{ $year }}" {{ ($year == $dataView['annoSelezionato'] ) ? " selected" : "" }}>{{ $year }}</option>
                                            @endforeach
                                        </select>
                                    <button type="submit" class="btn btn-primary">Cerca</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Campo nascosto per l'anno corrente -->
                   
                </form>
            </div>

            <div id="noLoad"></div>

            <!-- SUB-OBIETTIVO 2.1: Tempestività interventi a seguito di frattura femore su pazienti over 65 -->
            <div id="fatturaFemore" class="row justify-content-center">
                <div class="col-md-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-primary text-white">
                            <b>{{ __('Tempestivit&agrave; interventi a seguito di frattura femore su pazienti over 65') }}</b>
                            <br />
                            <small>{{ __('Interventi effettuati entro 0-2 giorni dal ricovero / numero totale di casi di frattura femore su pazienti over 65') }}</small>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <x-chartjs-component :chart="$dataView['chartFratturaFemore']" />
                                </div>
                                <div class="col-md-8 d-flex flex-column align-items-center justify-content-center">
                                    <div id="data-table1" class="w-100 mb-3">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Struttura</th>
                                                    <th>Numeratore</th>
                                                    <th>Denominatore</th>
                                                    <th>Percentuale</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($dataView['femore'] as $struttura => $rows)
                                                    @foreach($rows as $mese => $row)
                                                    <tr>
                                                        <td>{{ $row['struttura'] }}</td>
                                                        <td>{{ $row['numMaxFemore']  }}</td>
                                                        <td>{{ $row['denMaxFemore'] }}</td>
                                                        <td>{{ $row['percentualeFratturaFemore'] }} %</td>
                                                    </tr>
                                                    @endforeach
                                                @endforeach
                                            </tbody>
                                        </table>
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


            <div id="partoCesareoBox" class="row justify-content-center">
                <div class="col-md-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-primary text-white">
                            <b>{{ __('Riduzione incidenza parti cesarei primari (cesarei in donne senza nessun pregresso cesareo)') }}</b>
                            <br />
                            <small>{{ __('Parti cesarei di donne non precesarizzate (cesarei primari)/totale parti di donne con nessun pregresso cesareo') }}</small>
                        </div>
                        <div class="card-body">
                            <div id="partoCesareo500">
                                <h5 class="text-center text-secondary mb-3">
                                    {{ __('Presidi con meno di 1000 parti / anno') }}
                                </h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <x-chartjs-component :chart="$dataView['chartPartiCesareiMenoMille']" />
                                    </div>
                                    <div class="col-md-8 d-flex flex-column align-items-center justify-content-center">
                                        <div id="data-table500" class="w-100 mb-3">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Struttura</th>
                                                        <th>Num</th>
                                                        <th>Den</th>
                                                        <th>Percentuale</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($dataView['partiMinoreMille'] as $struttura => $rows)
                                                        @foreach($rows as $mese => $row)
                                                        <tr>
                                                            <td>{{ $row['struttura'] }}</td>
                                                            <td>{{ $row['numeratoreMaxPartiMinoreMille']  }}</td>
                                                            <td>{{ $row['denominatoreMaxPartiMinoreMille'] }}</td>
                                                            <td>{{ $row['percentualePartiMinoreMille'] }} %</td>
                                                        </tr>
                                                        @endforeach
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <br>

                            <hr id="separatore" class="my-4" style="display: none;">

                            <div id="partoCesareo1000">
                                <h5 class="text-center text-secondary mb-3">
                                    {{ __('Presidi con più di 1000 parti / anno') }}
                                </h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <x-chartjs-component :chart="$dataView['chartPartiCesareiMaggioriMille']" />
                                    </div>
                                    <div class="col-md-8 d-flex flex-column align-items-center justify-content-center">
                                        <div id="data-table1000" class="w-100 mb-3">
                                        <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Struttura</th>
                                                        <th>Num</th>
                                                        <th>Den</th>
                                                        <th>Percentuale</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($dataView['partiMaggioreMille'] as $struttura => $rows)
                                                        @foreach($rows as $mese => $row)
                                                        <tr>
                                                            <td>{{ $row['struttura'] }}</td>
                                                            <td>{{ $row['numeratoreMaxPartiMaggioreMille']  }}</td>
                                                            <td>{{ $row['denominatoreMaxPartiMaggioreMille'] }}</td>
                                                            <td>{{ $row['percentualePartiMaggioreMille'] }} %</td>
                                                        </tr>
                                                        @endforeach
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="legend p-3 border rounded mt-5">
                                <strong>Scala valori di riferimento (Punteggio massimo 4)</strong><br>
                                <span>Il raggiungimento dell'obiettivo &egrave; riconosciuto proporzionalmente
                                    al miglioramento relativo prodotto tra il valore minimo di partenza
                                    corrispondente alla soglia medio PNE AGENAS (30%) per ciascuno dei due
                                    livelli/soglia individuati e il rispettivo valore obiettivo.</span><br>
                                <span>Se il valore dell'indicatore &egrave; superiore al valore di partenza,
                                    l'obiettivo è considerato non raggiunto.</span><br />
                                <span>Se il valore è compreso tra il valore di partenza e il valore obiettivo,
                                    il grado di raggiungimento è riconosciuto applicando una funzione
                                    lineare.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="IMA" class="row justify-content-center">
                <div class="col-md-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-primary text-white">
                            <b>{{ __('Tempestivit&agrave; nell’effettuazione P.T.C.A. nei casi di I.M.A. STEMI') }}</b>
                            <br />
                            <small>{{ __('PTCA effettuate entro un intervallo temporale di 90 minuti dalla data di ricovero con diagnosi certa di I.M.A. STEMI / numero totale di I.M.A. STEMI diagnosticati') }}</small>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <x-chartjs-component :chart="$dataView['chartIma']" />
                                </div>
                                <div class="col-md-8 d-flex flex-column align-items-center justify-content-center">
                                    <div id="data-table3" class="w-100 mb-3">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Struttura</th>
                                                    <th>Num</th>
                                                    <th>Den</th>
                                                    <th>Percentuale</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($dataView['IMA'] as $struttura => $rows)
                                                    @foreach($rows as $mese => $row)
                                                    <tr>
                                                        <td>{{ $row['struttura'] }}</td>
                                                        <td>{{ $row['numeratoreMaxIma']  }}</td>
                                                        <td>{{ $row['denominatoreMaxIma'] }}</td>
                                                        <td>{{ $row['percentualeIma'] }} %</td>
                                                    </tr>
                                                    @endforeach
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <br>
                            <div class="legend p-3 border rounded">
                                <strong>Scala valori di riferimento (Punteggio massimo 4)</strong><br>
                                <span>Il raggiungimento dell'obiettivo &egrave; riconosciuto proporzionalmente
                                    al miglioramento relativo prodotto tra il valore minimo di partenza,
                                    corrispondente allo standard del DM 70 (60%) e il target nazionale
                                    (82%).</span><br>
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

            <div id="colectistectomia" class="row justify-content-center">
                <div class="col-md-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-primary text-white">
                            <b>{{ __('Proporzione di colecistectomia laparoscopica con degenza post-operatoria inferiore a 3 giorni') }}</b>
                            <br />
                            <small>{{ __('Numero di ricoveri con intervento di colecistectomia laparoscopica con degenza post-operatoria inferiore a 3 giorni / numero totale di ricoveri con intervento di colecistectomia laparoscopica.') }}</small>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <x-chartjs-component :chart="$dataView['chartColecistectomia']" />
                                </div>
                                <div class="col-md-8 d-flex flex-column align-items-center justify-content-center">
                                    <div id="data-table4" class="w-100 mb-3">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Struttura</th>
                                                    <th>Num</th>
                                                    <th>Den</th>
                                                    <th>Percentuale</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($dataView['colecisti'] as $struttura => $rows)
                                                    @foreach($rows as $mese => $row)
                                                    <tr>
                                                        <td>{{ $row['struttura'] }}</td>
                                                        <td>{{ $row['numeratoreMaxCole']  }}</td>
                                                        <td>{{ $row['denominatoreMaxCole'] }}</td>
                                                        <td>{{ $row['percentualeColecisti'] }} %</td>
                                                    </tr>
                                                    @endforeach
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <br>
                            <div class="legend p-3 border rounded">
                                <strong>Scala valori di riferimento (Punteggio massimo 4)</strong><br>
                                <span>Il raggiungimento dell'obiettivo &egrave; riconosciuto proporzionalmente
                                    al miglioramento relativo prodotto tra il valore minimo di partenza,
                                    corrispondente allo standard del DM 70 (70%) e il target nazionale
                                    (96%).</span><br>
                                <span>Se il valore dell'indicatore &egrave; minore o uguale al valore di
                                    partenza (70%), l'obiettivo è considerato non raggiunto.</span><br />
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
</div>

@endsection