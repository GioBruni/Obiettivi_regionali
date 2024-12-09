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
                    <!-- Titolo della sotto sezione con icona -->
                    <h4>
                        <i class="fas fa-bed-pulse"></i> <!-- Icona corrispondente all'obiettivo -->
                        {{ __('Obiettivo 2: Esiti') }} <!-- Testo del titolo -->
                    </h4>
                    <small>{{ __('') }}</small> <!-- Descrizione -->
                </div>

                <form action="{{ route('esiti') }}" method="GET" enctype="multipart/form-data">
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
                            <b>{{ __('Tempestività interventi a seguito di frattura femore su pazienti over 65') }}</b>
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
                                                    <th>Num</th>
                                                    <th>Den</th>
                                                    <th>Percentuale</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>{{ $dataView['nome_struttura']}}</td>
                                                    <td>{{$dataView['NumMaxFemore'] }}</td>
                                                    <td>{{$dataView['DenMaxFemore'] }}</td>
                                                    <td>{{ $dataView['percentualeFratturaFemore']}}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div id="message1"
                                        class="message bg-light p-3 rounded border border-primary text-center w-100">
                                        Messaggio
                                    </div>
                                </div>
                            </div>
                            <div class="legend p-3 border rounded mt-5">
                                <x-chartjs-component :chart="$dataView['chartFratturaFemoreLine']" />
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
                            <!-- Sezione I livello: < 1000 parti / anno -->
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
                                                    <tr>
                                                        <td>{{ $dataView['nome_struttura']}}</td>
                                                        <td>{{ $dataView['numeratoreMaxPartiMinoreMille']}}</td>
                                                        <td>{{ $dataView['denominatoreMaxPartiMinoreMille']}}</td>
                                                        <td>{{ $dataView['percentualePartiMinoreMille']}}</td>
                                                        
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div id="message500"
                                            class="message bg-light p-3 rounded border border-primary text-center w-100">
                                            messaggio punteggio
                                        </div>
                                    </div>
                                </div>
                                <div class="legend p-3 border rounded mt-5">
                                    <x-chartjs-component :chart="$dataView['chartPartiCesareiMinoriMilleLine']" />
                                </div>
                            </div>

                            <br>

                            <!-- Linea divisoria o separatore -->
                            <hr id="separatore" class="my-4" style="display: none;">

                            <!-- Sezione II livello: > 1000 parti / anno -->
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
                                                    <tr>
                                                        <td>{{ $dataView['nome_struttura']}}</td>
                                                        <td>{{ $dataView['numeratoreMaxPartiMaggioreMille']}}</td>
                                                        <td>{{ $dataView['denominatoreMaxPartiMaggioreMille']}}</td>
                                                        <td>{{ $dataView['percentualePartiMaggioreMille']}}</td>
                                                        
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div id="message1000"
                                            class="message bg-light p-3 rounded border border-primary text-center w-100">
                                            messaggio punteggio
                                        </div>
                                    </div>
                                </div>
                                <div class="legend p-3 border rounded mt-5">
                                    <x-chartjs-component :chart="$dataView['chartPartiCesareiMaggioriMilleLine']" />
                                </div>
                            </div>


                            <!-- Scala valori di riferimento (comune ai due livelli) -->
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

                            <div id="message1"
                                class="message bg-light p-3 rounded border border-primary text-center w-100">
                                Punteggio
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="IMA" class="row justify-content-center">
                <div class="col-md-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-primary text-white">
                            <b>{{ __('Tempestività nell’effettuazione P.T.C.A. nei casi di I.M.A. STEMI') }}</b>
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
                                                <tr>
                                                    <td>{{ $dataView['nome_struttura']}}</td>
                                                    <td>{{ $dataView['numeratoreMaxIma']}}</td>
                                                    <td>{{ $dataView['denominatoreMaxIma']}}</td>
                                                    <td>{{ $dataView['percentualeIma']}}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div id="message3"
                                        class="message bg-light p-3 rounded border border-primary text-center w-100">
                                        messaggio punteggio
                                    </div>
                                </div>
                            </div>
                            <div class="legend p-3 border rounded mt-5">
                                <x-chartjs-component :chart="$dataView['chartImaLine']" />
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
                                                <tr>
                                                    <td>{{ $dataView['nome_struttura']}}</td>
                                                    <td>{{ $dataView['numeratoreMaxCole']}}</td>
                                                    <td>{{ $dataView['denominatoreMaxCole']}}</td>
                                                    <td>{{ $dataView['percentualeCole']}}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div id="message4"
                                        class="message bg-light p-3 rounded border border-primary text-center w-100">
                                         messaggio punteggio
                                    </div>
                                </div>
                            </div>
                            <div class="legend p-3 border rounded mt-5">
                                <x-chartjs-component :chart="$dataView['chartColecistectomiaLine']" />
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