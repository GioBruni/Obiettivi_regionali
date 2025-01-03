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
                        {{ __('Obiettivo 5: Screening AO e AOU') }} <!-- Testo del titolo -->
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

                    <div id="adeguamento_dotazioni_organiche" class="row justify-content-center mt-3">
                        <div class="col-md-12">
                            <div class="card shadow-sm border-0">
                                <div class="card-header bg-primary text-white">
                                    <b>{{ __('Esecuzione delle prestazioni di approfondimento richieste dalla ASP') }}</b>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @if(isset($dataView['file']))
                                            @php
                                                $categoria_trovata = false;
                                            @endphp
                                            @foreach($dataView['file'] as $categoria_risultato)
                                                @if($categoria_risultato->category === 'Esecuzione delle prestazioni di approfondimento richieste dalla ASP')
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

                    <div id="formazione_utenti" class="row justify-content-center mt-3">
                        <div class="col-md-12">
                            <div class="card shadow-sm border-0">
                                <div class="card-header bg-primary text-white">
                                    <b>{{ __('Prenotazioni effettuate su richiesta dell utente al CUP dell AO direttamente sul programma gestionale degli screening dell ASP') }}</b>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @if(isset($dataView['file']))
                                            @php
                                                $categoria_trovata = false;
                                            @endphp

                                            @foreach($dataView['file'] as $categoria_risultato)
                                                @if($categoria_risultato->category === 'Prenotazioni effettuate su richiesta dell utente al CUP dell AO direttamente sul programma gestionale degli screening dell ASP')
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
                                    <b>{{ __('Partecipazione al PDTA screening e individuazione del referente clinico per ogni screening') }}</b>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @if(isset($dataView['file']))
                                            @php
                                                $categoria_trovata = false;
                                            @endphp
                                            @foreach($dataView['file'] as $categoria_risultato)
                                                @if($categoria_risultato->category === 'Partecipazione al PDTA screening e individuazione del referente clinico per ogni screening')
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
                                    <b>{{ __('Registrazione di tutti i dati dei casi inviati dalla ASP per approfondimento o terapia direttamente sul programma gestionale degli screening') }}</b>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @if(isset($dataView['file']))
                                            @php
                                                $categoria_trovata = false;
                                            @endphp
                                            @foreach($dataView['file'] as $categoria_risultato)

                                                @if($categoria_risultato->category === 'Registrazione di tutti i dati dei casi inviati dalla ASP per approfondimento o terapia direttamente sul programma gestionale degli screening')
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