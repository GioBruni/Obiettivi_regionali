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
                        <i class="fas fa-heartbeat"></i> <!-- Icona corrispondente all'obiettivo -->
                        {{ __('Obiettivo 5: Screening') }} <!-- Testo del titolo -->
                    </h4>
                    <small>{{ __('') }}</small> <!-- Descrizione -->
                </div>
                <div class="card-body">

                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-primary text-white">
                            <b>{{ __('Indicatori di risultato') }}</b>
                        </div>

                        <div class="row justify-content-center mt-4">
                            <div class="col-md-12 text-center">
                                <div style="width: 100%; margin: auto;">
                                    <x-chartjs-component :chart="$dataView['lineChart']" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <br>

                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-primary text-white">
                            <b>{{ __('Coinvolgimento e collaborazione MMG per il counseling e la prenotazione diretta dei pazienti in età target non-responder (%MMG aderenti)') }}</b>
                        </div>

                        <div class="row justify-content-center mt-4">
                            <div class="col-md-6 text-center">
                                <div style="width: 100%; max-width: 400px; margin: auto;">
                                    <x-chartjs-component :chart="$dataView['mmgChart']" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Totale MMG</th>
                                            <th>MMG Coinvolti</th>
                                            <th>Percentuale (%)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($dataView['tableData'] as $row)
                                            <tr>
                                                <td>{{ $row->mmg_totale }}</td>
                                                <td>{{ $row->mmg_coinvolti }}</td>
                                                <td>{{ number_format($dataView['percentualeAderenti'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                               

                            </div>
                        </div>
                        <br>
                        <div class="legend p-3 border rounded">
                            <strong>Scala valori di riferimento (Punteggio massimo 2)</strong><br>
                            <span>Se il valore dell'indicatore &egrave; maggiore del 60% l'obiettivo è pienamente
                                raggiunto (2
                                punti).</span><br />
                            <span>Se il valore dell'indicatore &egrave; compreso tra il 20% e il 60% l'obiettivo è
                                parzialmente raggiunto (1
                                punti).</span><br />
                            <span>Se il valore dell'indicatore &egrave; minore del 20% l'obiettivo è non raggiunto (0
                                punti).</span><br />
                        </div>
                    </div>

                    <br>

                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-primary text-white">
                            <b>{{ __('In applicazione della circolare prot. n. 42278 del 15/12/2022, regolamentazione dell\'accesso ai test di screening scoraggiando l\'uso opportunistico dei codici di esenzione D02 e D03') }}</b>
                        </div>

                        <div class="row justify-content-center mt-4">
                            <div class="col-md-6 text-center">
                                <div style="width: 100%; max-width: 400px; margin: auto;">
                                    <x-chartjs-component :chart="$dataView['codiciEsenzioneChart']" />
                                </div>
                            </div>

                            <div class="col-md-6">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>% prestazioni inappropriate</th>
                                            <th>Prestazioni inappropriate</th>
                                            <th>Totale Prestazioni</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>{{$dataView['percentuale']}}</td>
                                            <td>{{$dataView['numeratoreTotale']}}</td>
                                            <td>{{$dataView['denominatoreTotale']}}</td>
                                        </tr>
                                    </tbody>
                                </table>

                                <div class="legend p-3 border rounded mt-3 {{ $dataView['calcoloPunteggioOb5_2']['messaggioTmpCodiciDD']['classCodiciDD'] }}">
                                    <strong>{{ $dataView['calcoloPunteggioOb5_2']['messaggioTmpCodiciDD']['textCodiciDD'] }}</strong>
                                   
                                </div>

                            </div>
                        </div>
                        <br>
                        <div class="legend p-3 border rounded">
                            <strong>Scala valori di riferimento (Punteggio massimo 1)</strong><br>
                            <span>Se il valore dell'indicatore è compresa tra 0% e 10% l'obiettivo è pienamente
                                raggiunto (1
                                punti)</span><br />
                            <span>Se il valore dell'indicatore è maggiore dell'11% l'obiettivo è non raggiunto (0
                                punti).</span><br />
                        </div>
                    </div>

                    <div id="formazione_utenti" class="row justify-content-center mt-3">
                        <div class="col-md-12">
                            <div class="card shadow-sm border-0">
                                <div class="card-header bg-primary text-white">
                                    <b>{{ __('Formazione del Personale dedicato allo screening') }}</b>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @if(isset($dataView['file']))
                                            @php
                                                $categoria_trovata = false;
                                            @endphp

                                            @foreach($dataView['file'] as $categoria_risultato)
                                                @if($categoria_risultato->category === 'Formazione del personale dedicato allo screening')
                                                    @php
                                                        $categoria_trovata = true;
                                                    @endphp
                                                    @if($categoria_risultato->validator_user_id === null)
                                                        <div id="message5"
                                                            class="message bg-light p-3 rounded border border-primary text-center w-100"
                                                            style="color: orange;">
                                                            <strong>File caricato il:
                                                                {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $categoria_risultato->created_at)->format('d/m/Y H:i') }}
                                                                in attesa di approvazione.</strong>
                                                        </div>
                                                    @else
                                                        @if($categoria_risultato->approved === 1)
                                                            <div id="message5"
                                                                class="message bg-light p-3 rounded border border-primary text-center w-100"
                                                                style="color:green;">
                                                                <strong>Il file caricato il:
                                                                    {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $categoria_risultato->created_at)->format('d/m/Y H:i') }}
                                                                    è stato approvato -> Obiettivo raggiunto!</strong>
                                                            </div>
                                                        @else
                                                            <div id="message5"
                                                                class="message bg-light p-3 rounded border border-primary text-center w-100"
                                                                style="color:red;">
                                                                <strong>Il file caricato il:
                                                                    {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $categoria_risultato->created_at)->format('d/m/Y H:i') }}
                                                                    non è stato approvato -> Obiettivo non raggiunto!</strong>
                                                            </div>
                                                        @endif
                                                    @endif
                                                @endif
                                             @endforeach

                                            @if(!$categoria_trovata)
                                                <div id="message5"
                                                    class="message bg-light p-3 rounded border border-primary text-center w-100"
                                                    style="color:red;">
                                                    <strong>Il file non è ancora stato caricato -> Obiettivo non
                                                        raggiunto!</strong>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                    <div class="legend p-3 border rounded">
                                        <strong>Scala valori di riferimento (Punteggio massimo 1)</strong><br>
                                        <span>Se il valore dell'indicatore &egrave; superato, l'obiettivo è pienamente
                                            raggiunto (1
                                            punti).</span><br />
                                        <span>Se il valore dell'indicatore non &egrave; superato, l'obiettivo è non
                                            raggiunto (0
                                            punti).</span><br />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div id="adeguamento_dotazioni_organiche" class="row justify-content-center mt-3">
                        <div class="col-md-12">
                            <div class="card shadow-sm border-0">
                                <div class="card-header bg-primary text-white">
                                    <b>{{ __('Adeguamento delle dotazioni organiche') }}</b>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @if(isset($dataView['file']))
                                            @php
                                                $categoria_trovata = false;
                                            @endphp
                                            @foreach($dataView['file'] as $categoria_risultato)
                                                @if($categoria_risultato->category === 'Adeguamento delle dotazioni organiche')
                                                    @php
                                                        $categoria_trovata = true;
                                                    @endphp
                                                    @if($categoria_risultato->validator_user_id === null)
                                                        <div id="message5"
                                                            class="message bg-light p-3 rounded border border-primary text-center w-100"
                                                            style="color: orange;">
                                                            <strong>File caricato il:
                                                                {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $categoria_risultato->created_at)->format('d/m/Y H:i') }}
                                                                in attesa di approvazione.</strong>
                                                        </div>
                                                    @else
                                                        @if($categoria_risultato->approved === 1)
                                                            <div id="message5"
                                                                class="message bg-light p-3 rounded border border-primary text-center w-100"
                                                                style="color:green;">
                                                                <strong>Il file caricato il:
                                                                    {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $categoria_risultato->created_at)->format('d/m/Y H:i') }}
                                                                    è stato approvato -> Obiettivo raggiunto!</strong>
                                                            </div>
                                                        @else
                                                            <div id="message5"
                                                                class="message bg-light p-3 rounded border border-primary text-center w-100"
                                                                style="color:red;">
                                                                <strong>Il file caricato il:
                                                                    {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $categoria_risultato->created_at)->format('d/m/Y H:i') }}
                                                                    non è stato approvato -> Obiettivo non raggiunto!</strong>
                                                            </div>
                                                        @endif
                                                    @endif
                                                @endif
                                            @endforeach

                                            @if(!$categoria_trovata)
                                                <div id="message5"
                                                    class="message bg-light p-3 rounded border border-primary text-center w-100"
                                                    style="color:red;">
                                                    <strong>Il file non è ancora stato caricato -> Obiettivo non
                                                        raggiunto!</strong>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                    <div class="legend p-3 border rounded">
                                        <strong>Scala valori di riferimento (Punteggio massimo 1)</strong><br>
                                        <span>Se il valore dell'indicatore &egrave; superato, l'obiettivo è pienamente
                                            raggiunto (1
                                            punti).</span><br />
                                        <span>Se il valore dell'indicatore non &egrave; superato, l'obiettivo è non
                                            raggiunto (0
                                            punti).</span><br />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="programmi_comunicazione" class="row justify-content-center mt-3">
                        <div class="col-md-12">
                            <div class="card shadow-sm border-0">
                                <div class="card-header bg-primary text-white">
                                    <b>{{ __('Organizzazione di programmi di comunicazione rivolti alla popolazione target') }}</b>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @if(isset($dataView['file']))
                                            @php
                                                $categoria_trovata = false;
                                            @endphp
                                            @foreach($dataView['file'] as $categoria_risultato)

                                                @if($categoria_risultato->category === 'Organizzazione di programmi di comunicazione rivolti alla popolazione target')
                                                    @php
                                                        $categoria_trovata = true;

                                                    @endphp

                                                    @if($categoria_risultato->validator_user_id === null)
                                                        <div id="message5"
                                                            class="message bg-light p-3 rounded border border-primary text-center w-100"
                                                            style="color: orange;">
                                                            <strong>File caricato il:
                                                                {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $categoria_risultato->created_at)->format('d/m/Y H:i') }}
                                                                in attesa di approvazione.</strong>
                                                        </div>
                                                    @else
                                                        @if($categoria_risultato->approved === 1)
                                                            <div id="message5"
                                                                class="message bg-light p-3 rounded border border-primary text-center w-100"
                                                                style="color:green;">
                                                                <strong>Il file caricato il:
                                                                    {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $categoria_risultato->created_at)->format('d/m/Y H:i') }}
                                                                    è stato approvato -> Obiettivo raggiunto!</strong>
                                                            </div>
                                                        @else
                                                            <div id="message5"
                                                                class="message bg-light p-3 rounded border border-primary text-center w-100"
                                                                style="color:red;">
                                                                <strong>Il file caricato il:
                                                                    {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $categoria_risultato->created_at)->format('d/m/Y H:i') }}
                                                                    non è stato approvato -> Obiettivo non raggiunto!</strong>
                                                            </div>
                                                        @endif
                                                    @endif
                                                @endif
                                            @endforeach

                                            @if(!$categoria_trovata)
                                                <div id="message5"
                                                    class="message bg-light p-3 rounded border border-primary text-center w-100"
                                                    style="color:red;">
                                                    <strong>Il file non è ancora stato caricato -> Obiettivo non
                                                        raggiunto!</strong>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                    <div class="legend p-3 border rounded">
                                        <strong>Scala valori di riferimento (Punteggio massimo 1)</strong><br>
                                        <span>Se il valore dell'indicatore &egrave; superato, l'obiettivo è pienamente
                                            raggiunto (1
                                            punti).</span><br />
                                        <span>Se il valore dell'indicatore non &egrave; superato, l'obiettivo è non
                                            raggiunto (0
                                            punti).</span><br />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection