@extends('bootstrap-italia::page')

<?php
// Calcola le date di default
$dataInizioDefault = (new DateTime('first day of January this year'))->format('Y-m-d');
$dataFineDefault = (new DateTime())->format('Y-m-d');
?>
<style>
    .card {
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .card-header {
        font-size: 1.25rem;
    }

    #leaCoperturaVaccinaleCicloBase,
    #leaCoperturaVaccinaleMPR,
    #leaDecessiTumoreCP,
    #leaTumoreMammella,
    #canvasVet,
    #canvasAlimenti {
        max-width: 100%;
        height: auto;
    }

    #data-table1,
    #data-table2 {
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        overflow: hidden;
    }

    #message1,
    #message2 {
        background-color: #f8f9fa;
        border: 1px solid #007bff;
        padding: 15px;
        border-radius: 10px;
        margin-top: 20px;
    }

    .legend {
        font-size: 0.875rem;
        color: #333;
        text-align: left;
        margin-top: 15px;
        border: 1px solid #ddd;
        /* Aggiunta del bordo */
        border-radius: 5px;
        /* Angoli arrotondati per il bordo */
    }

    .legend strong {
        font-weight: bold;
    }

    #tumoreMammella,
    #DRG,
    #infezioniPostChirurgiche,
    #ospedalizzazioneEtaAdulta,
    #ospedalizzazioneEtaPediatrica,
    #decessiTumoreCP,
    #pazientiADI,
    #coperturaVaccinaleCicloBase,
    #coperturaVaccinaleMPR,
    #assistenzaOspedalieraBox,
    #assistenzaDistrettualeBox,
    #assistenzaPrevenzioneBox {
        display: none;
    }
</style>

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white text-center">
                    <!-- Titolo della sotto sezione con icona -->
                    <h4>
                        <i class="fas fa-tasks"></i> <!-- Icona corrispondente all'obiettivo -->
                        {{ __('Obiettivo 10: Garanzia dei LEA') }} <!-- Testo del titolo -->
                    </h4>
                    <small>{{ __('Area della Performance: garanzia dei LEA nell’Area della Prevenzione, dell’Assistenza Territoriale e dell’Assistenza Ospedaliera secondo il Nuovo Sistema di Garanzia (NSG)') }}</small> <!-- Descrizione -->
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="timeFilter">Intervallo di tempo:</label>
                            <select id="timeFilter" name="timeFilter" class="form-control">
                                <option value="1">Ultima settimana</option>
                                <option value="2">Ultimo mese</option>
                                <option value="3">Ultimi 3 mesi</option>
                                <option value="4">Ultimi 6 mesi</option>
                                <option value="5" selected>Ultimo anno</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="data_inizio">Data Inizio:</label>
                            <input type="date" id="data_inizio" class="form-control" value="{{ $dataInizioDefault }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="data_fine">Data Fine:</label>
                            <input type="date" id="data_fine" class="form-control" value="{{ $dataFineDefault }}">
                        </div>
                        <div class="col-md-3 mb-3 d-flex align-items-end">
                            <button id="searchButton" class="btn btn-primary">Cerca</button>
                        </div>
                    </div>

                    <!-- Spinner di caricamento -->
                    <div id="loading" class="m-auto text-center">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden"></span>
                        </div>
                        <div>
                            <p class="text-primary">Caricamento in corso...</p>
                        </div>
                    </div>

                    <div id="noLoad"></div>

                    <!-- INIZIO: Area della prevenzione -->
                    <div id="assistenzaPrevenzioneBox" class="row justify-content-center">
                        <div class="col-md-12">
                            <div class="card shadow-sm border-0">
                                <div class="card-header bg-primary text-white">
                                    <b>{{ __('AREA DELLA PREVENZIONE') }}</b>
                                    <br />
                                </div>

                                <div class="card-body">
                                    <div class="row">
                                        <!-- Filtro di selezione Presidio -->
                                        @if (!Auth::guest())
                                        @if (!auth()->user()->checkPresidio_user())
                                        <div class="col-md-4 mb-3">
                                            <label for="distretto_id2" class="form-label">
                                                <b>{{ __('Distretto:') }}</b>
                                            </label>
                                            <select id="distretto_id2" class="form-control">
                                                <option value="">{{ __('Tutti i distretti') }}</option>
                                                <option value="Augusta">{{ __('AUGUSTA') }}</option>
                                                <option value="Lentini">{{ __('LENTINI') }}</option>
                                                <option value="Noto">{{ __('NOTO') }}</option>
                                                <option value="Siracusa">{{ __('SIRACUSA') }}</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3 d-flex align-items-end">
                                            <button id="searchButton1" class="btn btn-primary">Cerca</button>
                                        </div>
                                        @endif
                                        @endif
                                    </div>
                                    <hr class="my-4">

                                    <!-- Copertura vaccinale nei bambini a 24 mesi per ciclo base (polio, difterite, tetano, epatite B, pertosse, Hib)  -->
                                    <div id="coperturaVaccinaleCicloBase">
                                        <h5 class="text-center text-secondary mb-3">
                                            {{ __('Copertura vaccinale nei bambini a 24 mesi per ciclo base (polio, difterite, tetano, epatite B, pertosse, Hib)') }}
                                        </h5>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <canvas id="leaCoperturaVaccinaleCicloBase"></canvas>
                                            </div>
                                            <div class="col-md-8 d-flex flex-column align-items-center justify-content-center">
                                                <div id="data-table1" class="w-100 mb-3">
                                                    <!-- La tabella sarà inserita qui -->
                                                </div>
                                                <div id="message1" class="message bg-light p-3 rounded border border-primary text-center w-100">
                                                    <!-- Il messaggio sarà inserito qui -->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="legend p-3 border rounded">
                                            <strong>Valore Soglia (Punteggio massimo 3)</strong><br>
                                            <span>Numeratore: numero di soggetti entro i 24 mesi di età, vaccinati con cicli completi (tre dosi). </span></br>
                                            <span>Denominatore: numero di soggetti della rispettiva coorte di nascita</span></br>
                                            <span>Fattore di scala: (x 100)</span></br></br>
                                            <strong>La soglia deve tendere ad un valore superiore al 95%</strong><br>
                                        </div>
                                    </div>

                                    <!-- Linea divisoria o separatore -->
                                    <hr id="separatore1" class="my-4">

                                    <!-- Copertura vaccinale nei bambini a 24 mesi per la 1° dose di vaccino contro morbillo, parotite, rosolia (MPR) -->
                                    <div id="coperturaVaccinaleMPR">
                                        <h5 class="text-center text-secondary mb-3">
                                            {{ __('Copertura vaccinale nei bambini a 24 mesi per la 1° dose di vaccino contro morbillo, parotite, rosolia (MPR)') }}
                                        </h5>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <canvas id="leaCoperturaVaccinaleMPR"></canvas>
                                            </div>
                                            <div class="col-md-8 d-flex flex-column align-items-center justify-content-center">
                                                <div id="data-table2" class="w-100 mb-3">
                                                    <!-- La tabella sarà inserita qui -->
                                                </div>
                                                <div id="message2" class="message bg-light p-3 rounded border border-primary text-center w-100">
                                                    <!-- Il messaggio sarà inserito qui -->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="legend p-3 border rounded">
                                            <strong>Valore Soglia (Punteggio massimo 3)</strong><br>
                                            <span>Numeratore: numero di soggetti entro i 24 mesi di età vaccinati con la 1° dose. </span></br>
                                            <span>Denominatore: numero di soggetti della rispettiva coorte di nascita</span></br>
                                            <span>Fattore di scala: (x 100)</span></br></br>
                                            <strong>La soglia deve tendere ad un valore superiore al 95%</strong><br>
                                        </div>
                                    </div>

                                    <!-- Linea divisoria o separatore -->
                                    <hr id="separatore2" class="my-4">

                                    <!-- Copertura vaccinale nei bambini a 24 mesi per la 1° dose di vaccino contro morbillo, parotite, rosolia (MPR) -->
                                    <div id="prevenzioneVeterinaria">
                                        <h5 class="text-center text-secondary mb-3">
                                            {{ __('Veterinaria') }}
                                        </h5>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <canvas id="canvasVet"></canvas>
                                            </div>
                                            <div class="col-md-8 d-flex flex-column align-items-center justify-content-center">
                                                <div id="data-table3" class="w-100 mb-3">
                                                    <!-- La tabella sarà inserita qui -->
                                                </div>
                                                <div id="message3" class="message bg-light p-3 rounded border border-primary text-center w-100">
                                                    <!-- Il messaggio sarà inserito qui -->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="legend p-3 border rounded">
                                            <strong>Valore Soglia (Punteggio massimo 3)</strong><br>
                                            <!-- <span>Numeratore: numero di soggetti entro i 24 mesi di età vaccinati con la 1° dose. </span></br>
                                            <span>Denominatore: numero di soggetti della rispettiva coorte di nascita</span></br>
                                            <span>Fattore di scala: (x 100)</span></br></br> -->
                                            <strong>La soglia deve tendere ad un valore superiore al 90%</strong><br>
                                        </div>
                                    </div>

                                    <!-- Linea divisoria o separatore -->
                                    <hr id="separatore3" class="my-4">

                                    <!-- Copertura vaccinale nei bambini a 24 mesi per la 1° dose di vaccino contro morbillo, parotite, rosolia (MPR) -->
                                    <div id="prevenzioneAlimenti">
                                        <h5 class="text-center text-secondary mb-3">
                                            {{ __('Alimenti') }}
                                        </h5>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <canvas id="canvasAlimenti"></canvas>
                                            </div>
                                            <div class="col-md-8 d-flex flex-column align-items-center justify-content-center">
                                                <div id="data-table4" class="w-100 mb-3">
                                                    <!-- La tabella sarà inserita qui -->
                                                </div>
                                                <div id="message4" class="message bg-light p-3 rounded border border-primary text-center w-100">
                                                    <!-- Il messaggio sarà inserito qui -->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="legend p-3 border rounded">
                                            <strong>Valore Soglia (Punteggio massimo 3)</strong><br>
                                            <!-- <span>Numeratore: numero di soggetti entro i 24 mesi di età vaccinati con la 1° dose. </span></br>
                                            <span>Denominatore: numero di soggetti della rispettiva coorte di nascita</span></br>
                                            <span>Fattore di scala: (x 100)</span></br></br> -->
                                            <strong>La soglia deve tendere ad un valore superiore al 80%</strong><br>
                                        </div>
                                    </div>

                                    <!-- Punteggio Complessivo -->
                                    <div id="punteggioComplessivoAreaPrevenzione" class="message bg-light p-3 rounded border border-primary text-center w-100 mt-5" style="display: none;">
                                        <strong><span id="punteggioTotaleAreaPrevenzione"></span></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- FINE: Area della prevenzione -->

                    <!-- INIZIO: Area dell'assistenza distrettuale -->
                    <div id="assistenzaDistrettualeBox" class="row justify-content-center">
                        <div class="col-md-12">
                            <div class="card shadow-sm border-0">
                                <div class="card-header bg-primary text-white">
                                    <b>{{ __('AREA DELL\'ASSISTENZA DISTRETTUALE') }}</b>
                                    <br />
                                </div>

                                <div class="card-body">
                                    <div class="row">
                                        <!-- Filtro di selezione Presidio -->
                                        @if (!Auth::guest())
                                        @if (!auth()->user()->checkDistretto_user())
                                        <div class="col-md-4 mb-3">
                                            <label for="distretto_id" class="form-label">
                                                <b>{{ __('Distretto:') }}</b>
                                            </label>
                                            <select id="distretto_id" class="form-control">
                                                <option value="">{{ __('Tutti i distretti') }}</option>
                                                <option value="Augusta">{{ __('AUGUSTA') }}</option>
                                                <option value="Lentini">{{ __('LENTINI') }}</option>
                                                <option value="Noto">{{ __('NOTO') }}</option>
                                                <option value="Siracusa">{{ __('SIRACUSA') }}</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3 d-flex align-items-end">
                                            <button id="searchButton2" class="btn btn-primary">Cerca</button>
                                        </div>
                                        @endif
                                        @endif
                                    </div>
                                    <hr class="my-4">

                                    <!-- Tasso di ospedalizzazione standardizzato in età adulta -->
                                    <div id="ospedalizzazioneEtaAdulta">
                                        <h5 class="text-center text-secondary mb-3">
                                            {{ __('Tasso di ospedalizzazione standardizzato in età adulta (≥ 18 anni) per: complicanze (a breve e lungo termine) per diabete, broncopneumopatia cronica ostruttiva (BPCO) e scompenso cardiaco') }}
                                        </h5>
                                        <div class="row">
                                            <div class="col-md-8">
                                                <canvas id="leaOspedalizzazioneEtaAdulta"></canvas>
                                            </div>
                                            <div class="col-md-4 d-flex flex-column align-items-center justify-content-center">
                                                <div id="message6" class="message bg-light p-3 rounded border border-primary text-center w-100">
                                                    <!-- Il messaggio sarà inserito qui -->
                                                </div>
                                                <div class="legend p-3 border rounded">
                                                    <strong>{{ __('Valore Soglia (Punteggio massimo 2)') }}</strong><br>
                                                    <span>L'indicatore complessivo è dato dalla somma dei tassi di ospedalizzazione (standardizzati) per patologia. Per ciascuna patologia il tasso è calcolato nel seguente modo: </span></br>
                                                    <span>Numeratore: N. dimissioni</span></br>
                                                    <span>Denominatore: Popolazione residente</span></br>
                                                    <span>Fattore di scala: x 100.000 abitanti.</span></br>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Linea divisoria o separatore -->
                                    <hr id="separatore6" class="my-4">

                                    <!-- Tasso di ospedalizzazione standardizzato (per 100.000 ab.)  in età pediatrica -->
                                    <div id="ospedalizzazioneEtaPediatrica">
                                        <h5 class="text-center text-secondary mb-3">
                                            {{ __('Tasso di ospedalizzazione standardizzato (per 100.000 ab.)  in età pediatrica (< 18 anni) per asma e gastroenterite') }}
                                        </h5>
                                        <div class="row">
                                            <div class="col-md-8">
                                                <canvas id="leaOspedalizzazioneEtaPediatrica"></canvas>
                                            </div>
                                            <div class="col-md-4 d-flex flex-column align-items-center justify-content-center">
                                                <div id="message7" class="message bg-light p-3 rounded border border-primary text-center w-100">
                                                    <!-- Il messaggio sarà inserito qui -->
                                                </div>
                                                <div class="legend p-3 border rounded">
                                                    <strong>{{ __('Valore Soglia (Punteggio massimo 2)') }}</strong><br>
                                                    <span>L'indicatore complessivo è dato dalla somma dei tassi di ospedalizzazione (standardizzati) per patologia. Per ciascuna patologia il tasso è calcolato nel seguente modo: </span></br>
                                                    <span>Numeratore: N. dimissioni</span></br>
                                                    <span>Denominatore: Popolazione residente</span></br>
                                                    <span>Fattore di scala: x 100.000 abitanti.</span></br>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Linea divisoria o separatore -->
                                    <hr id="separatore7" class="my-4">

                                    <!-- Tasso di pazienti trattati in ADI (CIA 1, CIA 2, CIA 3) -->
                                    <div id="pazientiADI">
                                        <h5 class="text-center text-secondary mb-3">
                                            {{ __('Tasso di pazienti trattati in ADI (CIA 1, CIA 2, CIA 3)') }}
                                        </h5>
                                        <div class="row">
                                            <div class="col-md-8">
                                                <!-- Primo grafico sulla prima riga -->
                                                <div class="row mb-3">
                                                    <div class="col-md-12">
                                                        <canvas id="leaPazientiADI1"></canvas>
                                                    </div>
                                                </div>
                                                <!-- Secondo grafico sulla seconda riga -->
                                                <div class="row mb-3">
                                                    <div class="col-md-12">
                                                        <canvas id="leaPazientiADI2"></canvas>
                                                    </div>
                                                </div>
                                                <!-- Terzo grafico sulla terza riga -->
                                                <div class="row mb-3">
                                                    <div class="col-md-12">
                                                        <canvas id="leaPazientiADI3"></canvas>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 d-flex flex-column align-items-center justify-content-center">
                                                <div id="message8" class="message bg-light p-3 rounded border border-primary text-center w-100">
                                                    <!-- Il messaggio sarà inserito qui -->
                                                </div>
                                                <div class="legend p-3 border rounded">
                                                    <strong>{{ __('Valore Soglia (Punteggio massimo 5)') }}</strong><br>
                                                    <strong>{{ __('CIA 1') }}</strong><br>
                                                    <span>Numeratore: Totale pazienti assistiti in cure domiciliari con intensità assistenziale CIA 1</span></br>
                                                    <span>Denominatore: Popolazione residente</span></br>
                                                    <span>Fattore di scala: (x 1.000) </span></br>
                                                    <strong>{{ __('CIA 2') }}</strong><br>
                                                    <span>Numeratore: Totale pazienti assistiti in cure domiciliari con intensità assistenziale CIA 2</span></br>
                                                    <span>Denominatore: Popolazione residente</span></br>
                                                    <span>Fattore di scala: (x 1.000) </span></br>
                                                    <strong>{{ __('CIA 3') }}</strong><br>
                                                    <span>Numeratore: Totale pazienti assistiti in cure domiciliari con intensità assistenziale CIA 1</span></br>
                                                    <span>Denominatore: Popolazione residente</span></br>
                                                    <span>Fattore di scala: (x 1.000) </span></br></br>
                                                    <strong>Il punteggio finale dell'indicatore è dato dalla somma pesata dei punteggi delle 3 componenti CIA1, CIA2 e CIA3, pesati rispettivamente con i valori 0,15, 0,35, 0,50.</strong></br>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <!-- Linea divisoria o separatore -->
                                    <hr id="separatore8" class="my-4">

                                    <!-- Numero deceduti per causa di tumore assistiti dalla Rete di cure palliative sul numero deceduti per causa di tumore -->
                                    <div id="decessiTumoreCP">
                                        <h5 class="text-center text-secondary mb-3">
                                            {{ __('Numero deceduti per causa di tumore assistiti dalla Rete di cure palliative sul numero deceduti per causa di tumore') }}
                                        </h5>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <canvas id="leaDecessiTumoreCP"></canvas>
                                            </div>
                                            <div class="col-md-8 d-flex flex-column align-items-center justify-content-center">
                                                <div id="data-table9" class="w-100 mb-3">
                                                    <!-- La tabella sarà inserita qui -->
                                                </div>
                                                <div id="message9" class="message bg-light p-3 rounded border border-primary text-center w-100">
                                                    <!-- Il messaggio sarà inserito qui -->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="legend p-3 border rounded">
                                            <strong>Valore Soglia (Punteggio massimo 3)</strong><br>
                                            <span>Numero deceduti per causa di tumore assistiti dalla Rete di cure palliative a domicilio o in hospice/numero deceduti per causa di tumore (Valore > 35%)</span></br>
                                            <span>Numeratore: Σ Assistiti in hospice con assistenza conclusa con decesso (Motivo conclusione valorizzato con 6) e per i quali la Patologia responsabile sia valorizzata con ICD-9-CM compreso tra 140-208 + Σ Assistiti in cure palliative domiciliari con assistenza conclusa per decesso (Motivo conclusione valorizzato con 3) per i quali la Patologia responsabile sia valorizzata con ICD-9-CM compreso tra 140-208. </span></br>
                                            <span>Denominatore: Media dei dati ISTAT di mortalità per causa tumore degli ultimi 3 anni disponibili.</span></br>
                                        </div>
                                    </div>

                                    <!-- Punteggio Complessivo -->
                                    <div id="punteggioComplessivoAreaTerritoriale" class="message bg-light p-3 rounded border border-primary text-center w-100 mt-5" style="display: none;">
                                        <strong><span id="punteggioTotaleAreaTerritoriale"></span></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- FINE: Area dell'assistenza distrettuale -->

                    <!-- INIZIO: Area dell'assistenza ospedaliera -->
                    <div id="assistenzaOspedalieraBox" class="row justify-content-center">
                        <div class="col-md-12">
                            <div class="card shadow-sm border-0">
                                <div class="card-header bg-primary text-white">
                                    <b>{{ __('AREA DELL\'ASSISTENZA OSPEDALIERA') }}</b>
                                    <br />
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Filtro di selezione Presidio -->
                                        @if (!Auth::guest())
                                        @if (!auth()->user()->checkPresidio_user())
                                        <div class="col-md-4 mb-3">
                                            <label for="servizio_id3" class="form-label">
                                                <b>{{ __('Presidio:') }}</b>
                                            </label>
                                            <select id="servizio_id3" class="form-control">
                                                <option value="">{{ __('Tutti i presidi') }}</option>
                                                <option value="19034500">{{ __('P.O. UMBERTO I SIRACUSA') }}</option>
                                                <option value="19034600">{{ __('P.O. AUGUSTA') }}</option>
                                                <option value="19034400">{{ __('P.O. NOTO') }}</option>
                                                <option value="19034300">{{ __('P.O. AVOLA') }}</option>
                                                <option value="19034700">{{ __('P.O. LENTINI') }}</option>
                                                <option value="19034800">{{ __('P.O. RIZZA') }}</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3 d-flex align-items-end">
                                            <button id="searchButton3" class="btn btn-primary">Cerca</button>
                                        </div>
                                        @endif
                                        @endif
                                    </div>
                                    <hr class="my-4">

                                    <!-- Interventi su tumori maligni della mammella -->
                                    <div id="tumoreMammella">
                                        <h5 class="text-center text-secondary mb-3">
                                            {{ __('Quota di interventi per tumore maligno della mammella eseguiti in reparti con volume di attività superiore a 150 (con 10% tolleranza) interventi annui') }}
                                        </h5>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <canvas id="leaTumoreMammella"></canvas>
                                            </div>
                                            <div class="col-md-8 d-flex flex-column align-items-center justify-content-center">
                                                <div id="data-table10" class="w-100 mb-3">
                                                    <!-- La tabella sarà inserita qui -->
                                                </div>
                                                <div id="message10" class="message bg-light p-3 rounded border border-primary text-center w-100">
                                                    <!-- Il messaggio sarà inserito qui -->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="legend p-3 border rounded">
                                            <strong>Valore Soglia (Punteggio massimo 2)</strong><br>
                                            <span>Proporzione di interventi per tumore maligno della mammella eseguiti in reparti con volume di attività superiore a 150 (con 10% tolleranza) annui.</span></br>
                                            <span>Numeratore: Numero di interventi chirurgici per tumore maligno della mammella in regime ordinario o day hospital, avvenuti in strutture italiane nell’anno di riferimento, con diagnosi principale o secondaria di tumore maligno della mammella (ICD-9-CM 174, 198.81, 233.0) ed intervento principale o secondario di quadrantectomia della mammella o mastectomia (ICD-9-CM 85.2x, 85.33, 85.34, 85.35, 85.36, 85.4.x) eseguiti in reparti con volume di attività superiore a 135 interventi annui. </span></br>
                                            <span>Denominatore: Numero di interventi chirurgici per tumore maligno della mammella in regime ordinario o day hospital, avvenuti in strutture italiane nell’anno di riferimento, con diagnosi principale o secondaria di tumore maligno della mammella (ICD-9-CM 174, 198.81, 233.0) ed intervento principale o secondario di quadrantectomia della mammella o mastectomia (ICD-9-CM 85.2x, 85.33, 85.34, 85.35, 85.36, 85.4.x). </span></br>
                                            <span>Fattore di scala: (x 100)</span></br>
                                        </div>
                                    </div>

                                    <!-- Linea divisoria o separatore -->
                                    <hr id="separatore10" class="my-4">

                                    <!-- Rapporto tra ricoveri attribuiti a DRG ad alto rischio di inappropriatezza -->
                                    <div id="DRG">
                                        <h5 class="text-center text-secondary mb-3">
                                            {{ __('Rapporto tra ricoveri attribuiti a DRG ad alto rischio di inappropriatezza e ricoveri attribuiti a DRG non a rischio di inappropriatezza in regime ordinario') }}
                                        </h5>
                                        <div class="row">
                                            <div class="col-md-8">
                                                <canvas id="DRGRischioInappropriatezza"></canvas>
                                            </div>
                                            <div class="col-md-4 d-flex flex-column align-items-center justify-content-center">
                                                <div id="message11" class="message bg-light p-3 rounded border border-primary text-center w-100">
                                                    <!-- Il messaggio sarà inserito qui -->
                                                </div>
                                                <div class="legend p-3 border rounded">
                                                    <strong>{{ __('Valore Soglia (Punteggio massimo 2)') }}</strong><br>
                                                    <ul style="list-style-type: none; padding: 0;">
                                                        <li><span style="color: red; font-weight: bold;">&mdash;</span>{{ __('Soglia oltre la quale l\'obiettivo viene considerato non raggiunto') }}</li>
                                                        <li><span style="color: orange; font-weight: bold;"><b>&mdash;</b></span>{{ __('Soglia oltre la quale l\'obiettivo viene considerato parzialmente raggiunto') }}</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Linea divisoria o separatore -->
                                    <hr id="separatore11" class="my-4">

                                    <!-- Frequenza di infezioni post-chirurgiche -->
                                    <div id="infezioniPostChirurgiche">
                                        <h5 class="text-center text-secondary mb-3">
                                            {{ __('Frequenza di infezioni post-chirurgiche') }}
                                        </h5>
                                        <div class="row">
                                            <div class="col-md-8">
                                                <canvas id="leaInfezioniPostChirurgiche"></canvas>
                                            </div>
                                            <div class="col-md-4 d-flex flex-column align-items-center justify-content-center">
                                                <div class="legend p-3 border rounded">
                                                    <strong>{{ __('Valore Soglia (Punteggio massimo 4)') }}</strong><br>
                                                    <span>{{ __('Verso dell\'indicatore: decrescente. Al diminuire del valore dell\'indicatore aumenta la garanzia del LEA.') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Punteggio Complessivo -->
                                    <div id="punteggioComplessivoAreaOspedaliera" class="message bg-light p-3 rounded border border-primary text-center w-100 mt-5" style="display: none;">
                                        <strong><span id="punteggioTotaleAreaOspedaliera"></span></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- FINE: Area dell'assistenza ospedaliera -->
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    let charts = {}; // Oggetto per tenere traccia dei grafici
    let nameTable = '';

    // Variabili globali per memorizzare i punteggi
    let nascite500 = null;
    let nascite1000 = null;

    // AREA OSPEDALIERA
    let haTumoreMammella = false;
    let haDRG = false;
    let haInfezioniPostChirurgiche = false;

    //Punteggi
    let punteggioTumoreMammella = null;
    let punteggioDRG = null;
    let punteggioInfezioniPostChirurgiche = null;

    //AREA DISTRETTUALE
    let haOspedalizzazioneEtaAdulta = false;
    let haOspedalizzazioneEtaPediatrica = false;
    let haDecessiTumoreCP = false;
    let haPazientiADI = false;

    //Punteggi
    let punteggioOspedalizzazioneEtaAdulta = null;
    let punteggioOspedalizzazioneEtaPediatrica = null;
    let punteggioDecessiTumoreCP = null;
    let punteggioPazientiADI = null;
    let punteggioCIA1 = null;
    let punteggioCIA2 = null;
    let punteggioCIA3 = null;

    //AREA PREVENZIONE
    let haCoperturaVaccinaleCicloBase = false;
    let haCoperturaVaccinaleMPR = false;
    let haVeterinaria = false;
    let haAlimenti = false;

    //Punteggi
    let punteggioCoperturaVaccinaleCicloBase = null;
    let punteggioCoperturaVaccinaleMPR = null;

    function fetchChartData(url, servizioId = '', dataInizio = '', dataFine = '') {
        return $.ajax({
            url: url,
            method: 'GET',
            data: {
                servizio_id: servizioId,
                data_inizio: dataInizio,
                data_fine: dataFine
            },
        }).then(function(response) {
            const data = response;

            // Nasconde l'elemento di caricamento e altre operazioni
            $('#loading').hide();
            console.info('success', url, data);

            // Calcolo delle somme
            let numeratore = 0;
            let denominatore = 0;
            let riga_finale = {};

            data.forEach(row => {
                numeratore += parseInt(row.numeratore);
                denominatore += parseInt(row.denominatore);
                if (url === '/chart-decessi-tumore-cp' || url === '/chart-copertura-vaccinale-ciclo-base' || url === '/chart-copertura-vaccinale-MPR')
                    denominatore = parseInt(row.denominatore);
            });

            let percentuale = ((numeratore / denominatore) * 100).toFixed(2);
            let percentuale_rimanente = (100 - percentuale).toFixed(2);


            let nameTable = "";
            if (url === '/chart-tumori-mammella') {
                nameTable = "#data-table10";
                if (data.length == 0) {
                    $('#tumoreMammella').hide();
                    haTumoreMammella = false;
                    punteggioTumoreMammella = null;
                } else {
                    $("#tumoreMammella").show();
                    haTumoreMammella = true;
                }
            } else if (url === '/chart-DRG') {
                if (data.length == 0) {
                    $('#DRG').hide();
                    haDRG = false;
                    punteggioDRG = null;
                } else {
                    $("#DRG").show();
                    haDRG = true;
                }
            } else if (url === '/chart-infezioni-post-chirurgiche') {
                if (data.length == 0) {
                    $('#infezioniPostChirurgiche').hide();
                    haInfezioniPostChirurgiche = false;
                    punteggioInfezioniPostChirurgiche = null;
                } else {
                    $("#infezioniPostChirurgiche").show();
                    haInfezioniPostChirurgiche = true;
                }
            } else if (url === '/chart-ospedalizzazione-eta-adulta') {
                if (data.length == 0) {
                    $('#ospedalizzazioneEtaAdulta').hide();
                    haOspedalizzazioneEtaAdulta = false;
                    punteggioOspedalizzazioneEtaAdulta = null;
                } else {
                    $("#ospedalizzazioneEtaAdulta").show();
                    haOspedalizzazioneEtaAdulta = true;
                }
            } else if (url === '/chart-ospedalizzazione-eta-pediatrica') {
                if (data.length == 0) {
                    $('#ospedalizzazioneEtaPediatrica').hide();
                    haOspedalizzazioneEtaPediatrica = false;
                    punteggioOspedalizzazioneEtaPediatrica = null;
                } else {
                    $("#ospedalizzazioneEtaPediatrica").show();
                    haOspedalizzazioneEtaPediatrica = true;
                }
            } else if (url === '/chart-ADI-CIA-1' || url === '/chart-ADI-CIA-2' || url === '/chart-ADI-CIA-3') {
                if (data.length == 0) {
                    $('#pazientiADI').hide();
                    haPazientiADI = false;
                    punteggioPazientiADI = null;
                } else {
                    $("#pazientiADI").show();
                    haPazientiADI = true;
                }
            } else if (url === '/chart-decessi-tumore-cp') {
                nameTable = "#data-table9";
                if (data.length == 0) {
                    $('#decessiTumoreCP').hide();
                    haDecessiTumoreCP = false;
                    punteggioDecessiTumoreCP = null;
                } else {
                    $("#decessiTumoreCP").show();
                    haDecessiTumoreCP = true;
                }
            } else if (url === '/chart-copertura-vaccinale-ciclo-base') {
                nameTable = "#data-table1";
                if (data.length == 0) {
                    $('#coperturaVaccinaleCicloBase').hide();
                    haCoperturaVaccinaleCicloBase = false;
                    punteggioCoperturaVaccinaleCicloBase = null;
                } else {
                    $("#coperturaVaccinaleCicloBase").show();
                    haCoperturaVaccinaleCicloBase = true;
                }
            } else if (url === '/chart-copertura-vaccinale-MPR') {
                nameTable = "#data-table2";
                if (data.length == 0) {
                    $('#coperturaVaccinaleMPR').hide();
                    haCoperturaVaccinaleMPR = false;
                    punteggioCoperturaVaccinaleMPR = null;
                } else {
                    $("#coperturaVaccinaleMPR").show();
                    haCoperturaVaccinaleMPR = true;
                }
            }

            checkVisibilityAreaOspedaliera();
            checkVisibilityAreaDistrettuale();
            checkVisibilityAreaPrevenzione();
            checkChartsVisibility();

            if (url === '/chart-DRG' || url === '/chart-infezioni-post-chirurgiche' || url === '/chart-ospedalizzazione-eta-adulta' || url === '/chart-ospedalizzazione-eta-pediatrica' ||
                url === '/chart-ADI-CIA-1' || url === '/chart-ADI-CIA-2' || url === '/chart-ADI-CIA-3')
                return data;


            if (url === '/chart-tumori-mammella') {
                var table = new Tabulator(nameTable, {
                    data: data,
                    layout: "fitColumns",
                    columns: [{
                            title: "Presidio",
                            field: "Presidio"
                        },
                        {
                            title: "Reparto",
                            field: "Reparto"
                        },
                        {
                            title: "Numeratore",
                            field: "numeratore",
                            bottomCalc: "sum"
                        },
                        {
                            title: "Denominatore",
                            field: "denominatore",
                            bottomCalc: "sum"
                        }
                    ],
                });


                riga_finale = {
                    Presidio: "Totale",
                    Reparto: "Totale",
                    numeratore: numeratore,
                    denominatore: denominatore,
                    percentuale: percentuale,
                    percentuale_rimanente: percentuale_rimanente
                };
            } else if (url === '/chart-decessi-tumore-cp' || url === '/chart-copertura-vaccinale-ciclo-base' || url === '/chart-copertura-vaccinale-MPR') {
                var table = new Tabulator(nameTable, {
                    data: data,
                    layout: "fitColumns",
                    columns: [{
                            title: "Distretto",
                            field: "Distretto"
                        },
                        {
                            title: "Numeratore",
                            field: "numeratore",
                            bottomCalc: "sum"
                        },
                        {
                            title: "Denominatore",
                            field: "denominatore"
                        }
                    ],
                });


                riga_finale = {
                    Distretto: "Totale",
                    numeratore: numeratore,
                    denominatore: denominatore,
                    percentuale: percentuale,
                    percentuale_rimanente: percentuale_rimanente
                };
            }

            // Ritorna il valore finale dalla Promessa
            return riga_finale;
        }).catch(function(error) {
            console.log('Errore nel recupero dei dati', error);
            return {
                percentuale_rimanente: 0,
                percentuale: 0
            };
        });
    }

    function fetchChartDataVetAlimenti(url) {
        return $.ajax({
            url: url,
            method: 'GET'
        }).then(function(response) {
            const data = response;

            // Nasconde l'elemento di caricamento e altre operazioni
            $('#loading').hide();
            console.info('successVetAlimenti', url, data, data.length);

            // Calcolo delle somme
            let numeratore = 0;
            let denominatore = 0;
            let riga_finale = {};

            let percentuale = 0;
            let percentuale_rimanente = 100;

            let nameTable = "";
            if (url === '/api/prevenzione_vet') {
                nameTable = "#data-table3";
                if (data.length == 0) {
                    console.info('nascondi');
                    $('#prevenzioneVeterinaria').hide();
                    haVeterinaria = false;
                    punteggioVeterinaria = null;
                } else {
                    $("#prevenzioneVeterinaria").show();
                    haVeterinaria = true;
                    percentuale = parseFloat(data[0].percentuale);
                    percentuale_rimanente = (100 - percentuale).toFixed(2);
                }
            } else if (url === '/api/prevenzione_alimenti') {
                nameTable = "#data-table4";
                if (data.length == 0) {
                    $('#prevenzioneAlimenti').hide();
                    haAlimenti = false;
                    punteggioAlimenti = null;
                } else {
                    $("#prevenzioneAlimenti").show();
                    haAlimenti = true;
                    percentuale = parseFloat(data[0].percentuale);
                    percentuale_rimanente = (100 - percentuale).toFixed(2);
                }
            }

            checkVisibilityAreaPrevenzione();
            checkChartsVisibility();

            var table = new Tabulator(nameTable, {
                data: data,
                layout: "fitColumns",
                columns: [{
                        title: "Trimestre",
                        field: "trimestre",
                        headerFilter: "input"
                    },
                    {
                        title: "Anno",
                        field: "anno",
                        headerFilter: "input"
                    },
                    {
                        title: "% Totale raggiunta",
                        field: "percentuale",
                    }
                ]
            });


            riga_finale = {
                Distretto: "Totale",
                percentuale: percentuale,
                percentuale_rimanente: percentuale_rimanente
            };


            // Ritorna il valore finale dalla Promessa
            return riga_finale;
        }).catch(function(error) {
            console.log('Errore nel recupero dei dati', error);
            return {
                percentuale_rimanente: 0,
                percentuale: 0
            };
        });
    }

    function createDoughnutChart(chartId, data, labels, title) {
        const ctx = document.getElementById(chartId).getContext('2d');

        if (charts[chartId]) {
            charts[chartId].destroy();
        }

        charts[chartId] = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: [data.percentuale_rimanente, data.percentuale],
                    backgroundColor: ['Red', '#0ec41a'],
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom', // Posiziona la legenda in basso
                        labels: {
                            padding: 35, // Aumenta lo spazio intorno alle etichette della legenda
                            boxWidth: 15, // Larghezza delle caselle di colore
                            font: {
                                size: 14, // Dimensione del font delle etichette della legenda
                            }
                        }
                    },
                    /*  title: {
                         display: true,
                         text: title,
                         font: {
                             size: 14, // Imposta la dimensione del font del titolo
                             weight: 'bold' // Puoi anche cambiare il peso del font
                         },
                         padding: {
                             top: 10,
                             bottom: 30
                         },
                         color: '#333'
                     }, */
                    datalabels: {
                        anchor: 'end',
                        align: 'end',
                        color: '#000', // Colore del testo
                        backgroundColor: 'transparent', // Sfondo trasparente
                        //borderColor: '#000', // Colore della linea di connessione
                        //borderWidth: 1, // Larghezza della linea di connessione
                        //borderRadius: 4,
                        padding: {
                            top: 6,
                            left: 6,
                            right: 6,
                            bottom: 6
                        },
                        formatter: function(value, context) {
                            return value + '%'; // Formattazione dell'etichetta
                        },
                        font: {
                            weight: 'bold',
                            size: 11
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });

        return charts[chartId];
    }

    function createLineChart(chartId, currentData, title, min, max, range) {
        const datasets = {}; // Oggetto per raccogliere i dati per reparto
        const labels = [];

        // Array con i nomi dei mesi
        const mesi = ['Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'];

        // Variabile per monitorare se ci sono presidi diversi
        let unicoPresidio = currentData[0]?.Presidio || null;
        let presidioCostante = true;

        // Controlla se il presidio è costante tra i dati
        currentData.forEach(row => {
            if (row.Presidio !== unicoPresidio) {
                presidioCostante = false;
            }
        });

        // Crea l'elenco di tutte le etichette (mesi) presenti nei dati
        currentData.forEach(row => {
            const mese = mesi[row.Mese - 1]; // Usa i nomi dei mesi, sottrai 1 perché l'array 'mesi' parte da 0
            if (!labels.includes(mese)) {
                labels.push(mese); // Assicurati di avere tutte le etichette (mesi)
            }
        });

        // Inizializza i dataset
        currentData.forEach(row => {
            const mese = mesi[row.Mese - 1]; // Usa i nomi dei mesi
            let etichetta;

            // Se il presidio è costante, usa il reparto come etichetta, altrimenti usa il presidio
            if (presidioCostante) {
                etichetta = row.Reparto;
            } else {
                etichetta = row.Presidio;
            }

            if (chartId == 'leaPazientiADI1' || chartId == 'leaPazientiADI2' || chartId == 'leaPazientiADI3' || chartId == 'leaOspedalizzazioneEtaAdulta' || chartId == 'leaOspedalizzazioneEtaPediatrica')
                etichetta = row.Presidio;

            // Se l'etichetta (presidio o reparto) non è stato ancora visto, inizializza il suo dataset
            let color = getRandomColor();

            if (!datasets[etichetta]) {
                datasets[etichetta] = {
                    label: etichetta,
                    data: Array(labels.length).fill(0), // Inizializza i valori a 0 per tutti i mesi
                    borderColor: color, // Funzione per generare un colore casuale
                    backgroundColor: color,
                    borderWidth: 2,
                    fill: false,
                    spanGaps: true // Mostra i valori a 0, senza lasciare spazi
                };
            }

            // Trova l'indice corrispondente al mese e aggiorna il dato
            const index = labels.indexOf(mese);
            if (index !== -1) {
                datasets[etichetta].data[index] = row.rapporto;
            }
        });

        // Converte l'oggetto datasets in un array di dataset
        const datasetsArray = Object.values(datasets);

        const data = {
            labels: labels, // I mesi come stringhe
            datasets: datasetsArray
        };

        if (charts[chartId]) {
            charts[chartId].destroy();
        }

        const ctx = document.getElementById(chartId).getContext('2d');

        // Configurazione delle annotazioni
        let annotationConfig = {};
        if (min !== 0 || max !== 0) {
            annotationConfig = {
                annotations: {
                    line1: {
                        type: 'line',
                        yMin: max, // Valore soglia
                        yMax: max, // Stessa soglia per una linea orizzontale
                        borderColor: 'red',
                        borderWidth: 2
                    },
                    line2: {
                        type: 'line',
                        yMin: min, // Valore soglia
                        yMax: min, // Stessa soglia per una linea orizzontale
                        borderColor: 'orange',
                        borderWidth: 2
                    }
                }
            };
        }

        charts[chartId] = new Chart(ctx, {
            type: 'line',
            data: data,
            options: {
                plugins: {
                    title: {
                        display: true, // Mostra il titolo
                        text: title // Il testo del titolo
                    },
                    legend: {
                        labels: {
                            usePointStyle: true,
                        },
                    },
                    annotation: annotationConfig
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: range || Math.max(...datasetsArray.map(d => Math.max(...d.data)), 100) // Valore massimo dinamico
                    }
                }
            }
        });

        return charts[chartId];
    }


    // Funzione per generare un colore casuale
    function getRandomColor() {
        let color;
        let brightness;

        do {
            // Genera tre componenti RGB
            const r = Math.floor(Math.random() * 256);
            const g = Math.floor(Math.random() * 256);
            const b = Math.floor(Math.random() * 256);

            // Converte in formato HEX
            color = `#${r.toString(16).padStart(2, '0')}${g.toString(16).padStart(2, '0')}${b.toString(16).padStart(2, '0')}`;

            // Calcola la luminosità percepita (brightness)
            brightness = (r * 299 + g * 587 + b * 114) / 1000; // Formula per la luminosità percepita

        } while (brightness < 100 || brightness > 200); // Evita colori troppo scuri o troppo chiari

        return color;
    }


    function updateMessage(elementId, data, thresholds) {
        const messageElement = document.getElementById(elementId);
        if (messageElement) {
            let messageText;
            let messageColor;
            let punteggio;

            if (elementId == 'message1') {
                //Copertura vaccinale nei bambini a 24 mesi per ciclo base (polio, difterite, tetano, epatite B, pertosse, Hib) 
                punteggio = calcolaPunteggioCoperturaVaccinale(data.percentuale);
                punteggioCoperturaVaccinaleCicloBase = punteggio;
                calcolaPunteggioAreaPrevenzione();
            } else if (elementId == 'message2') {
                //Copertura vaccinale nei bambini a 24 mesi per la 1° dose di vaccino contro morbillo, parotite, rosolia (MPR)  
                punteggio = calcolaPunteggioCoperturaVaccinale(data.percentuale);
                punteggioCoperturaVaccinaleMPR = punteggio;
                calcolaPunteggioAreaPrevenzione();
            } else if (elementId == 'message3') {
                //Copertura delle principali attività riferite al controllo delle anagrafi animali, della alimentazione degli animali da reddito e della somministrazione di farmaci ai fini delle garanzie di sicurezza alimentare per il cittadino
                punteggio = calcolaPunteggioVeterinaria(data.percentuale);
                punteggioVeterinaria = punteggio;
                calcolaPunteggioAreaPrevenzione();
            } else if (elementId == 'message4') {
                //Copertura delle principali attività di controllo per la contaminazione degli alimenti, con particolare riferimento alla ricerca di sostanze illecite, di residui di contaminanti, di farmaci, di fitofarmaci e di additivi negli alimenti di origine animale e vegetale  
                punteggio = calcolaPunteggioAlimenti(data.percentuale);
                punteggioAlimenti = punteggio;
                calcolaPunteggioAreaPrevenzione();
            } else if (elementId == 'message10') {
                //Tumore mammella
                punteggio = calcolaPunteggioTumoreMammella(data.percentuale, data.numeratore);
                punteggioTumoreMammella = punteggio;
                calcolaPunteggioAreaOspedaliera();
            } else if (elementId == 'message11') {
                //DRG ad alto rischio inappropriatezza
                punteggio = calcolaPunteggioDRG(data);
                punteggioDRG = punteggio;
                calcolaPunteggioAreaOspedaliera();
            } else if (elementId == 'message12') {
                //Frequenza infezioni post-chirurgiche
                calcolaPunteggioAreaOspedaliera();
            } else if (elementId == 'message6') {
                //Tasso di ospedalizzazione standardizzato in età adulta
                punteggio = calcolaPunteggioOspedalizzazione(data);
                punteggioOspedalizzazioneEtaAdulta = punteggio;
                calcolaPunteggioAreaTerritoriale();
            } else if (elementId == 'message7') {
                //Tasso di ospedalizzazione standardizzato in età pediatrica
                punteggio = calcolaPunteggioOspedalizzazione(data);
                punteggioOspedalizzazioneEtaPediatrica = punteggio;
                calcolaPunteggioAreaTerritoriale();
            } else if (elementId == 'message8') {
                //Tasso di pazienti trattati in ADI (CIA 1, CIA 2, CIA 3)
                punteggio = calcolaPunteggioADI(punteggioCIA1, punteggioCIA2, punteggioCIA3);
                punteggioPazientiADI = punteggio;
                calcolaPunteggioAreaTerritoriale();
            } else if (elementId == 'message9') {
                //Numero deceduti per causa di tumore assistiti dalla Rete di cure palliative sul numero deceduti per causa di tumore
                punteggio = calcolaPunteggioDecessiTumoreCP(data.percentuale);
                punteggioDecessiTumoreCP = punteggio;
                calcolaPunteggioAreaTerritoriale();
            }


            if (punteggio >= thresholds.full) {
                messageText = `Raggiungimento dell'obiettivo con punteggio: ${punteggio}`;
                messageColor = 'green';
            } else if (punteggio > thresholds.partial && punteggio < thresholds.full) {
                messageText = `Raggiungimento dell'obiettivo con punteggio: ${punteggio}`;
                messageColor = 'orange';
            } else {
                messageText = `Obiettivo non raggiunto con punteggio: ${punteggio}`;
                messageColor = 'red';
            }

            messageElement.innerHTML = `<strong>${messageText}</strong>`;
            messageElement.style.color = messageColor;
        }
    }

    function calcolaPunteggioTumoreMammella(percentuale, numeratore) {
        let y;

        if (numeratore < 135) {
            return 2;
        } else {
            if (percentuale >= 0 && percentuale <= 1.2702) {
                y = 0;
            } else if (percentuale > 1.2702 && percentuale < 90) {
                y = 1.2702 * Math.pow(10, -2) * Math.pow(percentuale, 2) - 3.2266 * Math.pow(10, -2) * percentuale + 2.0492 * Math.pow(10, -2);
            } else {
                y = 100;
            }

            return ((y * 2) / 100).toFixed(2);
        }
    }

    function calcolaPunteggioDRG(data) {
        let numeratore = 0;
        let denominatore = 0;
        let x = null;
        let y = null;

        data.forEach(function(d) {
            numeratore += parseFloat(d.numeratore);
            denominatore += parseFloat(d.denominatore);
        });

        x = (numeratore / denominatore).toFixed(3);

        if (x >= 0 && x < 0.15) {
            y = 100;
        } else if (x >= 0.15 && x < 0.375) {
            y = ((-444.4444 * x) + 166.6667).toFixed(2);
        } else {
            y = 0;
        }

        return ((y * 2) / 100).toFixed(2);
    }

    function calcolaPunteggioOspedalizzazione(data) {
        let numeratore = 0;
        let denominatore = 0;
        let x = null;
        let y = null;

        data.forEach(function(d) {
            numeratore += parseFloat(d.numeratore);
            denominatore = d.denominatore;
        });

        x = ((numeratore / denominatore) * 100000).toFixed(3);

        if (x >= 0 && x < 343) {
            y = 100;
        } else if (x >= 343 && x < 418) {
            y = ((-1.3333 * x) + 557.3333).toFixed(2);
        } else {
            y = 0;
        }

        return ((y * 2) / 100).toFixed(2);
    }

    function calcolaPunteggioCIA1(data) {
        let numeratore = 0;
        let denominatore = 0;
        let x = null;
        let punteggio = null;

        data.forEach(function(d) {
            numeratore += parseFloat(d.numeratore);
            denominatore = d.denominatore;
        });

        x = ((numeratore / denominatore) * 1000).toFixed(3);

        if (x < 0.5) {
            punteggio = 0;
        } else if (x >= 0.5 && x < 4) {
            punteggio = 28.5714 * x - 14.2857;
        } else if (x >= 4 && x < 6) {
            punteggio = 100;
        } else {
            punteggio = 100;
        }

        return Math.min(Math.max(punteggio, 0), 100); // Mantiene il punteggio tra 0 e 100
    }

    function calcolaPunteggioCIA2(data) {
        let numeratore = 0;
        let denominatore = 0;
        let x = null;
        let punteggio = null;

        data.forEach(function(d) {
            numeratore += parseFloat(d.numeratore);
            denominatore = d.denominatore;
        });

        x = ((numeratore / denominatore) * 1000).toFixed(3);

        if (x < 1) {
            punteggio = 0;
        } else if (x >= 1 && x < 2.5) {
            punteggio = 66.6667 * x - 66.6667;
        } else if (x >= 2.5 && x < 6) {
            punteggio = 100;
        } else {
            punteggio = 100;
        }

        return Math.min(Math.max(punteggio, 0), 100); // Mantiene il punteggio tra 0 e 100
    }

    function calcolaPunteggioCIA3(data) {
        let numeratore = 0;
        let denominatore = 0;
        let x = null;
        let punteggio = null;

        data.forEach(function(d) {
            numeratore += parseFloat(d.numeratore);
            denominatore = d.denominatore;
        });

        x = ((numeratore / denominatore) * 1000).toFixed(3);

        if (x < 0.75) {
            punteggio = 0;
        } else if (x >= 0.75 && x < 2) {
            punteggio = 80 * x - 60;
        } else if (x >= 2 && x < 6) {
            punteggio = 100;
        } else {
            punteggio = 100;
        }

        return Math.min(Math.max(punteggio, 0), 100); // Mantiene il punteggio tra 0 e 100
    }

    function calcolaPunteggioADI(punteggioCIA1, punteggioCIA2, punteggioCIA3) {
        // Calcola il punteggio finale pesato
        const punteggioFinale100 = (punteggioCIA1 * 0.15) + (punteggioCIA2 * 0.35) + (punteggioCIA3 * 0.50);

        // Porta il punteggio finale nella scala da 0 a 5
        const punteggioFinale = (punteggioFinale100 / 20).toFixed(2);

        return punteggioFinale;
    }


    function calcolaPunteggioDecessiTumoreCP(percentuale) {
        let y;
        if (percentuale >= 0 && percentuale < 5) {
            y = 0; // Obiettivo non raggiunto
        } else if (percentuale >= 5 && percentuale < 55) {
            y = 2 * percentuale - 10;
        } else {
            y = 100;
        }

        if (percentuale > 35)
            y = 100;

        return ((y * 3) / 100).toFixed(2);
    }

    function calcolaPunteggioCoperturaVaccinale(percentuale) {
        let y;
        if (percentuale >= 0 && percentuale < 90) {
            y = 0; // Obiettivo non raggiunto
        } else if (percentuale >= 90 && percentuale < 92) {
            y = 30 * percentuale - 2700;
        } else if (percentuale >= 92 && percentuale < 95) {
            y = 13.3333 * percentuale - 1166.6667;
        } else {
            y = 100;
        }

        return ((y * 3) / 100).toFixed(2);
    }

    function calcolaPunteggioVeterinaria(percentuale) {
        let y;
        if (percentuale >= 0 && percentuale < 70) {
            y = 0; // Obiettivo non raggiunto
        } else if (percentuale >= 70 && percentuale < 90) {
            y = (percentuale - 70) * (3 / 20); // Punteggio proporzionale tra 0 e 3
        } else {
            y = 3; // Punteggio pieno
        }

        return y.toFixed(2);
    }


    function calcolaPunteggioAlimenti(percentuale) {
        let y;
        if (percentuale >= 0 && percentuale < 80) {
            y = 0; // Obiettivo non raggiunto
        } else if (percentuale >= 80 && percentuale < 90) {
            y = (percentuale - 80) * (3 / 10); // Punteggio proporzionale tra 0 e 3
        } else {
            y = 3; // Punteggio pieno
        }

        return y.toFixed(2);
    }


    // Funzione che controlla se entrambi i punteggi sono pronti e aggiorna il punteggio complessivo
    function calcolaPunteggioAreaOspedaliera() {
        let messageText;
        let messageColor;

        if (haTumoreMammella && haDRG /*&& haInfezioniPostChirurgiche*/ ) { // Se entrambe le sezioni sono visibili
            // Calcolo del totale delle nascite
            let punteggioTotale = (parseFloat(punteggioTumoreMammella) || 0) +
                (parseFloat(punteggioDRG) || 0) +
                (parseFloat(punteggioInfezioniPostChirurgiche) || 0);

            punteggioTotale = (punteggioTotale).toFixed(2);

            if (punteggioTotale < 3) {
                messageText = `Obiettivo non raggiunto con punteggio: ${punteggioTotale}`;
                messageColor = 'red';
            } else if (punteggioTotale >= 3 && punteggioTotale <= 6) {
                messageText = `Raggiungimento dell'obiettivo con punteggio: ${punteggioTotale}`;
                messageColor = 'orange';
            } else {
                messageText = `Raggiungimento dell'obiettivo con punteggio: ${punteggioTotale}`;
                messageColor = 'green';
            }

            document.getElementById('punteggioTotaleAreaOspedaliera').innerText = messageText; // Aggiorna il punteggio complessivo
            document.getElementById('punteggioTotaleAreaOspedaliera').style.color = messageColor;
            document.getElementById('punteggioComplessivoAreaOspedaliera').style.display = 'block'; // Mostra il box del punteggio complessivo
        } else {
            document.getElementById('punteggioComplessivoAreaOspedaliera').style.display = 'none'; // Nascondi il punteggio complessivo
        }
    }

    function calcolaPunteggioAreaTerritoriale() {
        let messageText;
        let messageColor;

        if (!haOspedalizzazioneEtaAdulta && !haOspedalizzazioneEtaPediatrica && !haDecessiTumoreCP && !haPazientiADI) {
            document.getElementById('punteggioComplessivoAreaTerritoriale').style.display = 'none'; // Nascondi il punteggio complessivo
        } else {
            // Calcolo del totale delle nascite
            let punteggioTotale =
                (parseFloat(punteggioOspedalizzazioneEtaAdulta) || 0) +
                (parseFloat(punteggioOspedalizzazioneEtaPediatrica) || 0) +
                (parseFloat(punteggioDecessiTumoreCP) || 0) +
                (parseFloat(punteggioPazientiADI) || 0);

            punteggioTotale = (punteggioTotale).toFixed(2);

            if (punteggioTotale < 4) {
                messageText = `Obiettivo non raggiunto con punteggio: ${punteggioTotale}`;
                messageColor = 'red';
            } else if (punteggioTotale >= 4 && punteggioTotale <= 10) {
                messageText = `Raggiungimento dell'obiettivo con punteggio: ${punteggioTotale}`;
                messageColor = 'orange';
            } else {
                messageText = `Raggiungimento dell'obiettivo con punteggio: ${punteggioTotale}`;
                messageColor = 'green';
            }

            document.getElementById('punteggioTotaleAreaTerritoriale').innerText = messageText; // Aggiorna il punteggio complessivo
            document.getElementById('punteggioTotaleAreaTerritoriale').style.color = messageColor;
            document.getElementById('punteggioComplessivoAreaTerritoriale').style.display = 'block'; // Mostra il box del punteggio complessivo
        }
    }

    function calcolaPunteggioAreaPrevenzione() {
        let messageText;
        let messageColor;

        if (haCoperturaVaccinaleCicloBase && haCoperturaVaccinaleMPR && haVeterinaria && haAlimenti) {
            // Calcolo del totale delle nascite
            let punteggioTotale = (parseFloat(punteggioCoperturaVaccinaleCicloBase) || 0) +
                (parseFloat(punteggioCoperturaVaccinaleMPR) || 0) +
                (parseFloat(punteggioVeterinaria) || 0) +
                (parseFloat(punteggioAlimenti) || 0);

            punteggioTotale = (punteggioTotale).toFixed(2);

            if (punteggioTotale < 5) {
                messageText = `Obiettivo non raggiunto con punteggio: ${punteggioTotale}`;
                messageColor = 'red';
            } else if (punteggioTotale >= 5 && punteggioTotale <= 12) {
                messageText = `Raggiungimento dell'obiettivo con punteggio: ${punteggioTotale}`;
                messageColor = 'orange';
            } else {
                messageText = `Raggiungimento dell'obiettivo con punteggio: ${punteggioTotale}`;
                messageColor = 'green';
            }

            document.getElementById('punteggioTotaleAreaPrevenzione').innerText = messageText; // Aggiorna il punteggio complessivo
            document.getElementById('punteggioTotaleAreaPrevenzione').style.color = messageColor;
            document.getElementById('punteggioComplessivoAreaPrevenzione').style.display = 'block'; // Mostra il box del punteggio complessivo
        } else {
            document.getElementById('punteggioComplessivoAreaPrevenzione').style.display = 'none'; // Nascondi il punteggio complessivo
        }
    }

    function checkVisibilityAreaOspedaliera() {
        if (!haTumoreMammella && !haDRG && !haInfezioniPostChirurgiche) {
            // Se non ci sono dati per entrambe le sezioni, nascondi tutto il box
            document.getElementById('assistenzaOspedalieraBox').style.display = 'none';
        } else {
            // Mostra il box se almeno una sezione ha dati
            document.getElementById('assistenzaOspedalieraBox').style.display = 'block';
        }

        if (haTumoreMammella && haDRG && haInfezioniPostChirurgiche) {
            // Caso 1: Tutti sono true
            document.getElementById('separatore10').style.display = 'block'; // Visibile se haTumoreMammella e haDRG
            document.getElementById('separatore11').style.display = 'block'; // Visibile se haDRG o haTumoreMammella e haInfezioniPostChirurgiche
        } else if (haTumoreMammella && haDRG && !haInfezioniPostChirurgiche) {
            // Caso 2: Solo haInfezioniPostChirurgiche è false
            document.getElementById('separatore10').style.display = 'block'; // Visibile se haTumoreMammella e haDRG
            document.getElementById('separatore11').style.display = 'none'; // Non visibile, perché haInfezioniPostChirurgiche è false
        } else if (haTumoreMammella && !haDRG && haInfezioniPostChirurgiche) {
            // Caso 3: Solo haDRG è false
            document.getElementById('separatore10').style.display = 'none'; // Non visibile, perché haDRG è false
            document.getElementById('separatore11').style.display = 'block'; // Visibile se haTumoreMammella e haInfezioniPostChirurgiche
        } else if (haTumoreMammella && !haDRG && !haInfezioniPostChirurgiche) {
            // Caso 4: Solo haTumoreMammella è true
            document.getElementById('separatore10').style.display = 'none'; // Non visibile, perché haDRG è false
            document.getElementById('separatore11').style.display = 'none'; // Non visibile, perché haInfezioniPostChirurgiche è false
        } else if (!haTumoreMammella && haDRG && haInfezioniPostChirurgiche) {
            // Caso 5: Solo haTumoreMammella è false
            document.getElementById('separatore10').style.display = 'none'; // Non visibile, perché haTumoreMammella è false
            document.getElementById('separatore11').style.display = 'block'; // Visibile se haDRG e haInfezioniPostChirurgiche
        } else if (!haTumoreMammella && haDRG && !haInfezioniPostChirurgiche) {
            // Caso 6: Solo haDRG è true
            document.getElementById('separatore10').style.display = 'none'; // Non visibile, perché haTumoreMammella è false
            document.getElementById('separatore11').style.display = 'none'; // Non visibile, perché haInfezioniPostChirurgiche è false
        } else if (!haTumoreMammella && !haDRG && haInfezioniPostChirurgiche) {
            // Caso 7: Solo haInfezioniPostChirurgiche è true
            document.getElementById('separatore10').style.display = 'none'; // Non visibile, perché haTumoreMammella è false
            document.getElementById('separatore11').style.display = 'none'; // Non visibile, perché haDRG è false
        } else {
            // Caso 8: Tutti sono false
            document.getElementById('separatore10').style.display = 'none'; // Non visibile, perché haTumoreMammella e haDRG sono false
            document.getElementById('separatore11').style.display = 'none'; // Non visibile, perché haDRG o haInfezioniPostChirurgiche è false
        }
    }

    function checkVisibilityAreaDistrettuale() {
        if (!haOspedalizzazioneEtaAdulta && !haOspedalizzazioneEtaPediatrica && !haDecessiTumoreCP && !haPazientiADI) {
            // Se non ci sono dati per tutte le sezioni, nascondi tutto il box
            document.getElementById('assistenzaDistrettualeBox').style.display = 'none';
        } else {
            // Mostra il box se almeno una sezione ha dati
            document.getElementById('assistenzaDistrettualeBox').style.display = 'block';
        }

        if (haOspedalizzazioneEtaAdulta && haOspedalizzazioneEtaPediatrica && haDecessiTumoreCP && haPazientiADI) {
            // Caso 1: Tutti sono true
            document.getElementById('separatore6').style.display = 'block';
            document.getElementById('separatore7').style.display = 'block';
            document.getElementById('separatore8').style.display = 'block';
        } else if (haOspedalizzazioneEtaAdulta && haOspedalizzazioneEtaPediatrica && haDecessiTumoreCP && !haPazientiADI) {
            // Caso 2: haPazientiADI è false
            document.getElementById('separatore6').style.display = 'block';
            document.getElementById('separatore7').style.display = 'none';
            document.getElementById('separatore8').style.display = 'block';
        } else if (haOspedalizzazioneEtaAdulta && haOspedalizzazioneEtaPediatrica && !haDecessiTumoreCP && haPazientiADI) {
            // Caso 3: haDecessiTumoreCP è false
            document.getElementById('separatore6').style.display = 'block';
            document.getElementById('separatore7').style.display = 'block';
            document.getElementById('separatore8').style.display = 'none';
        } else if (haOspedalizzazioneEtaAdulta && haOspedalizzazioneEtaPediatrica && !haDecessiTumoreCP && !haPazientiADI) {
            // Caso 4: haDecessiTumoreCP e haPazientiADI sono false
            document.getElementById('separatore6').style.display = 'block';
            document.getElementById('separatore7').style.display = 'none';
            document.getElementById('separatore8').style.display = 'none';
        } else if (haOspedalizzazioneEtaAdulta && !haOspedalizzazioneEtaPediatrica && haDecessiTumoreCP && haPazientiADI) {
            // Caso 5: haOspedalizzazioneEtaPediatrica è false
            document.getElementById('separatore6').style.display = 'none';
            document.getElementById('separatore7').style.display = 'block';
            document.getElementById('separatore8').style.display = 'block';
        } else if (haOspedalizzazioneEtaAdulta && !haOspedalizzazioneEtaPediatrica && haDecessiTumoreCP && !haPazientiADI) {
            // Caso 6: haOspedalizzazioneEtaPediatrica e haPazientiADI sono false
            document.getElementById('separatore6').style.display = 'none';
            document.getElementById('separatore7').style.display = 'none';
            document.getElementById('separatore8').style.display = 'block';
        } else if (haOspedalizzazioneEtaAdulta && !haOspedalizzazioneEtaPediatrica && !haDecessiTumoreCP && haPazientiADI) {
            // Caso 7: haOspedalizzazioneEtaPediatrica e haDecessiTumoreCP sono false
            document.getElementById('separatore6').style.display = 'none';
            document.getElementById('separatore7').style.display = 'block';
            document.getElementById('separatore8').style.display = 'none';
        } else if (haOspedalizzazioneEtaAdulta && !haOspedalizzazioneEtaPediatrica && !haDecessiTumoreCP && !haPazientiADI) {
            // Caso 8: solo haOspedalizzazioneEtaAdulta è true
            document.getElementById('separatore6').style.display = 'none';
            document.getElementById('separatore7').style.display = 'none';
            document.getElementById('separatore8').style.display = 'none';
        } else if (!haOspedalizzazioneEtaAdulta && haOspedalizzazioneEtaPediatrica && haDecessiTumoreCP && haPazientiADI) {
            // Caso 9: haOspedalizzazioneEtaAdulta è false
            document.getElementById('separatore6').style.display = 'none';
            document.getElementById('separatore7').style.display = 'block';
            document.getElementById('separatore8').style.display = 'block';
        } else if (!haOspedalizzazioneEtaAdulta && haOspedalizzazioneEtaPediatrica && haDecessiTumoreCP && !haPazientiADI) {
            // Caso 10: haOspedalizzazioneEtaAdulta e haPazientiADI sono false
            document.getElementById('separatore6').style.display = 'none';
            document.getElementById('separatore7').style.display = 'none';
            document.getElementById('separatore8').style.display = 'block';
        } else if (!haOspedalizzazioneEtaAdulta && haOspedalizzazioneEtaPediatrica && !haDecessiTumoreCP && haPazientiADI) {
            // Caso 11: haDecessiTumoreCP è false
            document.getElementById('separatore6').style.display = 'none';
            document.getElementById('separatore7').style.display = 'block';
            document.getElementById('separatore8').style.display = 'none';
        } else if (!haOspedalizzazioneEtaAdulta && haOspedalizzazioneEtaPediatrica && !haDecessiTumoreCP && !haPazientiADI) {
            // Caso 12: solo haOspedalizzazioneEtaPediatrica è true
            document.getElementById('separatore6').style.display = 'none';
            document.getElementById('separatore7').style.display = 'none';
            document.getElementById('separatore8').style.display = 'none';
        } else if (!haOspedalizzazioneEtaAdulta && !haOspedalizzazioneEtaPediatrica && haDecessiTumoreCP && haPazientiADI) {
            // Caso 13: haOspedalizzazioneEtaAdulta e haOspedalizzazioneEtaPediatrica sono false
            document.getElementById('separatore6').style.display = 'none';
            document.getElementById('separatore7').style.display = 'block';
            document.getElementById('separatore8').style.display = 'none';
        } else if (!haOspedalizzazioneEtaAdulta && !haOspedalizzazioneEtaPediatrica && haDecessiTumoreCP && !haPazientiADI) {
            // Caso 14: haPazientiADI è false
            document.getElementById('separatore6').style.display = 'none';
            document.getElementById('separatore7').style.display = 'none';
            document.getElementById('separatore8').style.display = 'none';
        }
    }

    function checkVisibilityAreaPrevenzione() {
        if (!haCoperturaVaccinaleCicloBase && !haCoperturaVaccinaleMPR) {
            // Se non ci sono dati per entrambe le sezioni, nascondi tutto il box
            document.getElementById('assistenzaPrevenzioneBox').style.display = 'none';
        } else {
            // Mostra il box se almeno una sezione ha dati
            document.getElementById('assistenzaPrevenzioneBox').style.display = 'block';
        }

        if (haCoperturaVaccinaleCicloBase && haCoperturaVaccinaleMPR) {
            // Caso 1: Tutti sono true
            document.getElementById('separatore1').style.display = 'block';
        } else {
            // Caso 8: Tutti sono false
            document.getElementById('separatore1').style.display = 'none';
        }
    }


    function checkChartsVisibility() {
        // Controlla se le chart sono visibili
        //Area ospedaliera
        const isTumoreMammella = $('#tumoreMammella').is(':visible');
        const isDRG = $('#DRG').is(':visible');
        const isInfezioniPostChirurgiche = $('#infezioniPostChirurgiche').is(':visible');
        //Area territoriale
        const isOspedalizzazioneEtaAdulta = $('#ospedalizzazioneEtaAdulta').is(':visible');
        const isOspedalizzazioneEtaPediatrica = $('#ospedalizzazioneEtaPediatrica').is(':visible');
        const isPazientiADI = $('#pazientiADI').is(':visible');
        const isDecessiTumoreCP = $('#decessiTumoreCP').is(':visible');
        //Area prevenzione
        const isCoperturaVaccinaleCicloBase = $('#coperturaVaccinaleCicloBase').is(':visible');
        const isCoperturaVaccinaleMPR = $('#coperturaVaccinaleMPR').is(':visible');
        const isVeterinaria = $('prevenzioneVeterinaria').is(':visible');
        const isAlimenti = $('prevenzioneAlimenti').is(':visible');

        // Se nessuna delle chart è visibile, mostra il div #noLoad
        if (!isTumoreMammella && !isDRG && !isInfezioniPostChirurgiche && !isOspedalizzazioneEtaAdulta && !isOspedalizzazioneEtaPediatrica && isPazientiADI && !isDecessiTumoreCP &&
            !isCoperturaVaccinaleCicloBase && !isCoperturaVaccinaleMPR && !isVeterinaria && !isAlimenti) {
            $('#noLoad').html("<div class='alert alert-warning text-center' role='alert'>Dati non presenti per il presidio selezionato.</div>").show();
        } else {
            $('#noLoad').hide(); // Nasconde il div se almeno una chart è visibile
        }
    }



    async function updateChart(chartId, messageId, url, labels, title, thresholds, servizioId = '', dataInizio = '', dataFine = '') {
        // Utilizzo della funzione
        let data;
        if (url != '/api/prevenzione_vet' && url != '/api/prevenzione_alimenti') {
            data = await fetchChartData(url, servizioId, dataInizio, dataFine);
        } else {
            data = await fetchChartDataVetAlimenti(url);
        }

        if (url == '/chart-DRG') {
            createLineChart(chartId, data, title, 0.15, 0.375, 1);
        } else if (url == '/chart-infezioni-post-chirurgiche') {
            createLineChart(chartId, data, title, 0, 0, 20000);
        } else if (url == '/chart-ospedalizzazione-eta-adulta') {
            createLineChart(chartId, data, title, 343, 418, 50);
        } else if (url == '/chart-ospedalizzazione-eta-pediatrica') {
            createLineChart(chartId, data, title, 343, 418, 50);
        } else if (url == '/chart-ADI-CIA-1') {
            createLineChart(chartId, data, title, 0, 0, 2);
            punteggioCIA1 = calcolaPunteggioCIA1(data);
        } else if (url == '/chart-ADI-CIA-2') {
            createLineChart(chartId, data, title, 0, 0, 2);
            punteggioCIA2 = calcolaPunteggioCIA2(data);
        } else if (url == '/chart-ADI-CIA-3') {
            createLineChart(chartId, data, title, 0, 0, 2);
            punteggioCIA3 = calcolaPunteggioCIA3(data);
        } else {
            createDoughnutChart(chartId, data, labels, title);
        }
        updateMessage(messageId, data, thresholds);
    }


    $(document).ready(function() {

        $('#searchButton').click(function() {
            const dataInizio = $('#data_inizio').val();
            const dataFine = $('#data_fine').val();
            const servizioId2 = $('#servizio_id2').val();
            const servizioId3 = $('#servizio_id3').val();
            const distrettoId = $('#distretto_id').val();
            const distrettoId2 = $('#distretto_id2').val();

            // Chiamata alla funzione per aggiornare i grafici e i dati
            //Area della prevenzione
            updateChart('leaCoperturaVaccinaleCicloBase', 'message1', '/chart-copertura-vaccinale-ciclo-base',
                ['Non vaccinati', 'Vaccinati'],
                ['Copertura vaccinale ciclo base'], {
                    full: 2.5,
                    partial: 0
                }, distrettoId2, dataInizio, dataFine);
            updateChart('leaCoperturaVaccinaleMPR', 'message2', '/chart-copertura-vaccinale-MPR',
                ['Non vaccinati', 'Vaccinati'],
                ['Copertura vaccinale MPR'], {
                    full: 2.5,
                    partial: 0
                }, distrettoId2, dataInizio, dataFine);
            updateChart('canvasVet', 'message3', '/api/prevenzione_vet',
                ['Non raggiunta', 'Raggiunta'],
                ['Veterinaria'], {
                    full: 2.5,
                    partial: 0
                });
            updateChart('canvasAlimenti', 'message4', '/api/prevenzione_alimenti',
                ['Non raggiunta', 'Raggiunta'],
                ['Alimenti'], {
                    full: 2.5,
                    partial: 0
                });

            //Area territoriale
            updateChart('leaOspedalizzazioneEtaAdulta', 'message6', '/chart-ospedalizzazione-eta-adulta',
                [''],
                ['Tasso di ospedalizzazione standardizzato in età adulta'], {
                    full: 1.5,
                    partial: 0
                }, servizioId2, dataInizio, dataFine);
            updateChart('leaOspedalizzazioneEtaPediatrica', 'message7', '/chart-ospedalizzazione-eta-pediatrica',
                [''],
                ['Tasso di ospedalizzazione standardizzato in età pediatrica'], {
                    full: 1.5,
                    partial: 0
                }, servizioId2, dataInizio, dataFine);
            updateChart('leaPazientiADI1', 'message8', '/chart-ADI-CIA-1',
                [''],
                ['Pazienti trattati con CIA 1'], {
                    full: 4,
                    partial: 0
                }, distrettoId, dataInizio, dataFine);
            updateChart('leaPazientiADI2', 'message8', '/chart-ADI-CIA-2',
                [''],
                ['Pazienti trattati con CIA 2'], {
                    full: 4,
                    partial: 0
                }, distrettoId, dataInizio, dataFine);
            updateChart('leaPazientiADI3', 'message8', '/chart-ADI-CIA-3',
                [''],
                ['Pazienti trattati con CIA 3'], {
                    full: 4,
                    partial: 0
                }, distrettoId, dataInizio, dataFine);
            updateChart('leaDecessiTumoreCP', 'message9', '/chart-decessi-tumore-cp',
                ['Decessi non in cure palliative', 'Decessi in cure palliative'],
                ['Decessi per tumore assistiti dalla Rete di cure palliative'], {
                    full: 2.5,
                    partial: 0
                }, distrettoId, dataInizio, dataFine);

            //Area ospedaliera
            updateChart('leaTumoreMammella', 'message10', '/chart-tumori-mammella',
                ['Interventi in reparti con volume > 135', 'Interventi complessivi'],
                ['Quota interventi per tumore maligno della mammella'], {
                    full: 1.5,
                    partial: 0
                }, servizioId3, dataInizio, dataFine);
            updateChart('DRGRischioInappropriatezza', 'message11', '/chart-DRG',
                [''],
                ['Rapporto DRG ad alto rischio inappropriatezza'], {
                    full: 1.5,
                    partial: 0
                }, servizioId3, dataInizio, dataFine);
            updateChart('leaInfezioniPostChirurgiche', 'message12', '/chart-infezioni-post-chirurgiche',
                [''],
                ['Frequenza di infezioni post-chirurgiche'], {
                    full: 3.5,
                    partial: 0
                }, servizioId3, dataInizio, dataFine);
        });

        $('#searchButton1').click(function() {
            const distrettoId2 = $('#distretto_id2').val();
            const dataInizio = $('#data_inizio').val();
            const dataFine = $('#data_fine').val();

            // Chiamata alla funzione per aggiornare i grafici e i dati
            updateChart('leaCoperturaVaccinaleCicloBase', 'message1', '/chart-copertura-vaccinale-ciclo-base',
                ['Non vaccinati', 'Vaccinati'],
                ['Copertura vaccinale ciclo base'], {
                    full: 2.5,
                    partial: 0
                }, distrettoId2, dataInizio, dataFine);
            updateChart('leaCoperturaVaccinaleMPR', 'message2', '/chart-copertura-vaccinale-MPR',
                ['Non vaccinati', 'Vaccinati'],
                ['Copertura vaccinale MPR'], {
                    full: 2.5,
                    partial: 0
                }, distrettoId2, dataInizio, dataFine);
        });

        $('#searchButton2').click(function() {
            const distrettoId = $('#distretto_id').val();
            const dataInizio = $('#data_inizio').val();
            const dataFine = $('#data_fine').val();

            // Chiamata alla funzione per aggiornare i grafici e i dati
            updateChart('leaOspedalizzazioneEtaAdulta', 'message6', '/chart-ospedalizzazione-eta-adulta',
                [''],
                ['Tasso di ospedalizzazione standardizzato in età adulta'], {
                    full: 1.5,
                    partial: 0
                }, distrettoId, dataInizio, dataFine);
            updateChart('leaOspedalizzazioneEtaPediatrica', 'message7', '/chart-ospedalizzazione-eta-pediatrica',
                [''],
                ['Tasso di ospedalizzazione standardizzato in età pediatrica'], {
                    full: 1.5,
                    partial: 0
                }, distrettoId, dataInizio, dataFine);
            updateChart('leaPazientiADI1', 'message8', '/chart-ADI-CIA-1',
                [''],
                ['Pazienti trattati con CIA 1'], {
                    full: 4,
                    partial: 0
                }, distrettoId, dataInizio, dataFine);
            updateChart('leaPazientiADI2', 'message8', '/chart-ADI-CIA-2',
                [''],
                ['Pazienti trattati con CIA 2'], {
                    full: 4,
                    partial: 0
                }, distrettoId, dataInizio, dataFine);
            updateChart('leaPazientiADI3', 'message8', '/chart-ADI-CIA-3',
                [''],
                ['Pazienti trattati con CIA 3'], {
                    full: 4,
                    partial: 0
                }, distrettoId, dataInizio, dataFine);
            updateChart('leaDecessiTumoreCP', 'message9', '/chart-decessi-tumore-cp',
                ['Decessi non in cure palliative', 'Decessi in cure palliative'],
                ['Decessi per tumore assistiti dalla Rete di cure palliative'], {
                    full: 2.5,
                    partial: 0
                }, distrettoId, dataInizio, dataFine);
        });

        $('#searchButton3').click(function() {
            const servizioId = $('#servizio_id3').val();
            const dataInizio = $('#data_inizio').val();
            const dataFine = $('#data_fine').val();

            // Chiamata alla funzione per aggiornare i grafici e i dati
            updateChart('leaTumoreMammella', 'message10', '/chart-tumori-mammella',
                ['Interventi in reparti con volume > 135', 'Interventi complessivi'],
                ['Quota interventi per tumore maligno della mammella'], {
                    full: 1.5,
                    partial: 0
                }, servizioId, dataInizio, dataFine);
            updateChart('DRGRischioInappropriatezza', 'message11', '/chart-DRG',
                [''],
                ['Rapporto DRG ad alto rischio inappropriatezza'], {
                    full: 1.5,
                    partial: 0
                }, servizioId, dataInizio, dataFine);
            updateChart('leaInfezioniPostChirurgiche', 'message12', '/chart-infezioni-post-chirurgiche',
                [''],
                ['Frequenza di infezioni post-chirurgiche'], {
                    full: 3.5,
                    partial: 0
                }, servizioId, dataInizio, dataFine);
        });

        $('#timeFilter').change(function() {
            var selectedValue = this.value;
            var today = new Date();
            var startDate, endDate;

            switch (selectedValue) {
                case '1':
                    startDate = new Date();
                    startDate.setDate(today.getDate() - 7);
                    endDate = today;
                    break;
                case '2':
                    startDate = new Date();
                    startDate.setMonth(today.getMonth() - 1);
                    endDate = today;
                    break;
                case '3':
                    startDate = new Date();
                    startDate.setMonth(today.getMonth() - 3);
                    endDate = today;
                    break;
                case '4':
                    startDate = new Date();
                    startDate.setMonth(today.getMonth() - 6);
                    endDate = today;
                    break;
                case '5':
                    startDate = new Date();

                    // Imposta il primo giorno dell'anno corrente
                    startDate.setMonth(0); // Mese di gennaio (0 = gennaio)
                    startDate.setDate(1); // Primo giorno del mese
                    endDate = today;
                    break;
                default:
                    startDate = null;
                    endDate = null;
                    break;
            }

            if (startDate && endDate) {
                document.getElementById('data_inizio').value = startDate.toISOString().split('T')[0];
                document.getElementById('data_fine').value = endDate.toISOString().split('T')[0];
            }
        });


        // Inizializza i grafici al caricamento della pagina
        //Area prevenzione
        updateChart('leaCoperturaVaccinaleCicloBase', 'message1', '/chart-copertura-vaccinale-ciclo-base', ['Non vaccinati', 'Vaccinati'], ['Copertura vaccinale ciclo base'], {
            full: 2.5,
            partial: 0
        }, '', $('#data_inizio').val(), $('#data_fine').val());
        updateChart('leaCoperturaVaccinaleMPR', 'message2', '/chart-copertura-vaccinale-MPR', ['Non vaccinati', 'Vaccinati'], ['Copertura vaccinale MPR'], {
            full: 2.5,
            partial: 0
        }, '', $('#data_inizio').val(), $('#data_fine').val());
        updateChart('canvasVet', 'message3', '/api/prevenzione_vet',
            ['Non raggiunta', 'Raggiunta'],
            ['Veterinaria'], {
                full: 2.5,
                partial: 0
            });
        updateChart('canvasAlimenti', 'message4', '/api/prevenzione_alimenti',
            ['Non raggiunta', 'Raggiunta'],
            ['Alimenti'], {
                full: 2.5,
                partial: 0
            });

        //Area territoriale
        updateChart('leaOspedalizzazioneEtaAdulta', 'message6', '/chart-ospedalizzazione-eta-adulta', [''], ['Tasso di ospedalizzazione standardizzato in età adulta'], {
            full: 1.5,
            partial: 0
        }, '', $('#data_inizio').val(), $('#data_fine').val());
        updateChart('leaOspedalizzazioneEtaPediatrica', 'message7', '/chart-ospedalizzazione-eta-pediatrica', [''], ['Tasso di ospedalizzazione standardizzato in età pediatrica'], {
            full: 1.5,
            partial: 0
        }, '', $('#data_inizio').val(), $('#data_fine').val());
        updateChart('leaPazientiADI1', 'message8', '/chart-ADI-CIA-1', [''], ['Pazienti trattati con CIA 1'], {
            full: 4,
            partial: 0
        }, '', $('#data_inizio').val(), $('#data_fine').val());
        updateChart('leaPazientiADI2', 'message8', '/chart-ADI-CIA-2', [''], ['Pazienti trattati con CIA 2'], {
            full: 4,
            partial: 0
        }, '', $('#data_inizio').val(), $('#data_fine').val());
        updateChart('leaPazientiADI3', 'message8', '/chart-ADI-CIA-3', [''], ['Pazienti trattati con CIA 3'], {
            full: 4,
            partial: 0
        }, '', $('#data_inizio').val(), $('#data_fine').val());
        updateChart('leaDecessiTumoreCP', 'message9', '/chart-decessi-tumore-cp', ['Decessi non in cure palliative', 'Decessi in cure palliative'], ['Decessi per tumore assistiti dalla Rete di cure palliative'], {
            full: 2.5,
            partial: 0
        }, '', $('#data_inizio').val(), $('#data_fine').val());

        //Area ospedaliera
        updateChart('leaTumoreMammella', 'message10', '/chart-tumori-mammella', ['Interventi in reparti con volume > 135', 'Interventi complessivi'], ['Quota interventi per tumore maligno della mammella'], {
            full: 1.5,
            partial: 0
        }, '', $('#data_inizio').val(), $('#data_fine').val());
        updateChart('DRGRischioInappropriatezza', 'message11', '/chart-DRG', ['Interventi in reparti con volume > 135', 'Interventi complessivi'], ['Rapporto DRG ad alto rischio inappropriatezza'], {
            full: 1.5,
            partial: 0
        }, '', $('#data_inizio').val(), $('#data_fine').val());
        updateChart('leaInfezioniPostChirurgiche', 'message12', '/chart-infezioni-post-chirurgiche', [''], ['Frequenza di infezioni post-chirurgiche'], {
            full: 3.5,
            partial: 0
        }, '', $('#data_inizio').val(), $('#data_fine').val());


        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });


    });
</script>

@endsection