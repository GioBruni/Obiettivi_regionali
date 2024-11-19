@extends('bootstrap-italia::page')

@section("bootstrapitalia_js")
<script src="{{ asset("js/chart.js") }}"></script>
@endsection

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white text-center">
                    <h4>
                        <i class="fas fa-tasks"></i>
                        {{ __('Obiettivo 10: Garanzia dei LEA') }}
                    </h4>
                    <small>{{ __('Area della Performance: garanzia dei LEA nell’Area della Prevenzione, dell’Assistenza Territoriale e dell’Assistenza Ospedaliera secondo il Nuovo Sistema di Garanzia (NSG)') }}</small>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('aggiornaGraficiGaranzia') }}">
                        @csrf
                        <div class="card-body">
                            <div class="row">

                                <div class="col-md-5 mb-3">
                                    <label for="data_inizio">Data Inizio:</label>
                                    <input type="date" name="data_inizio" id="data_inizio" class="form-control"
                                        value={{$dataView['dataInizioDefault']}}>
                                </div>
                                <div class="col-md-5 mb-3">
                                    <label for="data_fine">Data Fine:</label>
                                    <input type="date" name="data_fine" id="data_fine" class="form-control"
                                        value={{$dataView['dataFineDefault']}}>
                                </div>
                                <div class="col-md-2 mb-3 d-flex align-items-end">
                                    <button id="searchButton" class="btn btn-primary w-100">Cerca</button>
                                </div>
                            </div>
                        </div>
                        <div id="assistenzaPrevenzioneBox" class="row justify-content-center">
                            <div class="col-md-12">
                                <div class="card shadow-sm border-0">
                                    <div class="card-header bg-primary text-white">
                                        <b>{{ __('AREA DELLA PREVENZIONE') }}</b>
                                        <br />
                                    </div>
                                    <div class="card-body">
                                        <hr class="my-4">

                                        <div class="row">

                                            <h5 class="text-center text-secondary mb-3">
                                                {{ __('Copertura vaccinale nei bambini a 24 mesi per ciclo base (polio, difterite, tetano, epatite B, pertosse, Hib)') }}
                                            </h5>

                                            <div class="row justify-content-center mt-4">
                                                <div class="col-md-6 text-center">
                                                    <div style="width: 100%; max-width: 300px; margin: auto;">
                                                        <x-chartjs-component :chart="$dataView['areaPrevenzione']" />
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <table class="table table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th>Distretto</th>
                                                                <th>Numeratore</th>
                                                                <th>Denominatore</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($dataView['primoGrafico'] as $dati)
                                                                <tr>
                                                                    <td>{{ $dati->nome_struttura ?? 'null' }}</td>
                                                                    <td>{{ $dati->ob10_1 ?? 'null' }}</td>
                                                                    <td>{{250}}</td>

                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                    <div
                                                        class="legend p-3 border rounded mt-3 {{ $dataView['messaggioTmp']['class'] }}">
                                                        <strong>{{ $dataView['messaggioTmp']['text'] }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                            <br>
                                            <div class="legend p-3 border rounded">
                                                <strong>Valore Soglia (Punteggio massimo 3)</strong><br>
                                                <span>Numeratore: numero di soggetti entro i 24 mesi di età, vaccinati
                                                    con
                                                    cicli completi (tre dosi). </span></br>
                                                <span>Denominatore: numero di soggetti della rispettiva coorte di
                                                    nascita</span></br>
                                                <span>Fattore di scala: (x 100)</span></br></br>
                                                <strong>La soglia deve tendere ad un valore superiore al
                                                    95%</strong><br>
                                            </div>

                                        </div>

                                        <hr class="my-4">

                                        <h5 class="text-center text-secondary mb-3">
                                            {{ __('Copertura vaccinale nei bambini a 24 mesi per la 1° dose di vaccino contro morbillo, parotite, rosolia (MPR)') }}
                                        </h5>

                                        <div class="row justify-content-center mt-4">
                                            <div class="col-md-6 text-center">
                                                <div style="width: 100%; max-width: 300px; margin: auto;">
                                                    <x-chartjs-component
                                                        :chart="$dataView['areaPrevenzionePrimaDose']" />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <table class="table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>Distretto</th>
                                                            <th>Numeratore</th>
                                                            <th>Denominatore</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($dataView['prevenzioneDue'] as $dati)
                                                            <tr>
                                                                <td>{{ $dati->nome_struttura ?? 'null' }}</td>
                                                                <td>{{ $dati->ob10_2 ?? 'null' }}</td>
                                                                <td>{{ 100 }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                                <div
                                                    class="legend p-3 border rounded mt-3 {{ $dataView['messaggioTmpPrevenzioneDue']['classPrevenzioneDue'] }}">
                                                    <strong>{{ $dataView['messaggioTmpPrevenzioneDue']['textPrevenzioneDue'] }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                        <br>
                                        <div class="legend p-3 border rounded">
                                            <strong>Valore Soglia (Punteggio massimo 3)</strong><br>
                                            <span>Numeratore: numero di soggetti entro i 24 mesi di età vaccinati con la
                                                1°
                                                dose. </span></br>
                                            <span>Denominatore: numero di soggetti della rispettiva coorte di
                                                nascita</span></br>
                                            <span>Fattore di scala: (x 100)</span></br></br>
                                            <strong>La soglia deve tendere ad un valore superiore al 95%</strong><br>
                                        </div>

                                        <hr class="my-4">
                                    </div>


                                    <h5 class="text-center text-secondary mb-3">
                                        {{ __('Copertura delle principali attività riferite al controllo delle anagrafi animali, della alimentazione degli animali da reddito e della somministrazione di farmaci ai fini delle garanzie di sicurezza alimentare per il cittadino') }}
                                    </h5>

                                    <div class="row justify-content-center mt-4">
                                        <div class="col-md-6 text-center">
                                            <div style="width: 100%; max-width: 300px; margin: auto;">
                                                <x-chartjs-component :chart="$dataView['Veterinaria']" />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Distretto</th>
                                                        <th>Numeratore</th>
                                                        <th>Denominatore</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($dataView['prevenzioneTre'] as $dati)
                                                        <tr>
                                                            <td>{{ $dati->nome_struttura}}</td>
                                                            <td>{{ $dati->ob10_3 }}</td>
                                                            <td>{{ 150 }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                            <div
                                                class="legend p-3 border rounded mt-3 {{ $dataView['messaggioTmpPrevenzioneTre']['classPrevenzioneTre'] }}">
                                                <strong>{{ $dataView['messaggioTmpPrevenzioneTre']['textPrevenzioneTre'] }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="legend p-3 border rounded">
                                        <strong>Valore Soglia (Punteggio massimo 3)</strong><br>
                                        <!-- <span>Numeratore: numero di soggetti entro i 24 mesi di età vaccinati con la 1° dose. </span></br>
                                <span>Denominatore: numero di soggetti della rispettiva coorte di nascita</span></br>
                                <span>Fattore di scala: (x 100)</span></br></br> -->
                                        <strong>La soglia deve tendere ad un valore superiore al 80%</strong><br>
                                    </div>
                                </div>
                            </div>
                        </div>


                    </form>

                    <div class="col-md-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-primary text-white">
                                <b>{{ __('AREA DELL\'ASSISTENZA DISTRETTUALE') }}</b>
                                <br />
                            </div>
                            <br>
                            <div id="ospedalizzazioneEtaAdulta">
                                <h5 class="text-center text-secondary mb-3">
                                    {{ __('Tasso di ospedalizzazione standardizzato in età adulta (≥ 18 anni) per: complicanze (a breve e lungo termine) per diabete, broncopneumopatia cronica ostruttiva (BPCO) e scompenso cardiaco') }}
                                </h5>
                                <div class="row">
                                    <div class="col-md-8">
                                        <x-chartjs-component :chart="$dataView['ospedalizzazioneAdulta']" />
                                    </div>
                                    <div class="col-md-4 d-flex flex-column align-items-center justify-content-center">
                                        <div id="message6"
                                            class="message bg-light p-3 rounded border border-primary text-center w-100">
                                            Qui metterò il punteggio
                                        </div>
                                        <div class="legend p-3 border rounded">
                                            <strong>{{ __('Valore Soglia (Punteggio massimo 2)') }}</strong><br>
                                            <span>L'indicatore complessivo è dato dalla somma dei tassi di
                                                ospedalizzazione
                                                (standardizzati)
                                                per patologia. Per ciascuna patologia il tasso è calcolato nel seguente
                                                modo: </span></br>
                                            <span>Numeratore: N. dimissioni</span></br>
                                            <span>Denominatore: Popolazione residente</span></br>
                                            <span>Fattore di scala: x 100.000 abitanti.</span></br>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr id="separatore6" class="my-4">


                            <div id="ospedalizzazioneEtaPediatrica">
                                <h5 class="text-center text-secondary mb-3">
                                    {{ __('Tasso di ospedalizzazione standardizzato (per 100.000 ab.)  in età pediatrica (< 18 anni) per asma e gastroenterite') }}
                                </h5>
                                <div class="row">
                                    <div class="col-md-8">
                                        <x-chartjs-component :chart="$dataView['asmaGastroenterite']" />
                                    </div>
                                    <div class="col-md-4 d-flex flex-column align-items-center justify-content-center">
                                        <div id="message7"
                                            class="message bg-light p-3 rounded border border-primary text-center w-100">
                                            qui metterò il punteggio
                                        </div>
                                        <div class="legend p-3 border rounded">
                                            <strong>{{ __('Valore Soglia (Punteggio massimo 2)') }}</strong><br>
                                            <span>L'indicatore complessivo è dato dalla somma dei tassi di
                                                ospedalizzazione
                                                (standardizzati)
                                                per patologia. Per ciascuna patologia il tasso è calcolato nel seguente
                                                modo: </span></br>
                                            <span>Numeratore: N. dimissioni</span></br>
                                            <span>Denominatore: Popolazione residente</span></br>
                                            <span>Fattore di scala: x 100.000 abitanti.</span></br>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr id="separatore7" class="my-4">


                            <div id="pazientiADI">
                                <h5 class="text-center text-secondary mb-3">
                                    {{ __('Tasso di pazienti trattati in ADI (CIA 1, CIA 2, CIA 3)') }}
                                </h5>
                                <div class="row">
                                    <div class="col-md-8">
                                        <!-- Primo grafico -->
                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <x-chartjs-component :chart="$dataView['CIA1'] " />
                                            </div>
                                        </div>
                                        <!-- Secondo grafico  -->
                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <x-chartjs-component :chart="$dataView['CIA2'] " />
                                            </div>
                                        </div>
                                        <!-- Terzo grafico-->
                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <x-chartjs-component :chart="$dataView['CIA3'] " />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 d-flex flex-column align-items-center justify-content-center">
                                        <div id="message8"
                                            class="message bg-light p-3 rounded border border-primary text-center w-100">
                                            Qui inserirò il punteggio
                                        </div>
                                        <div class="legend p-3 border rounded">
                                            <strong>{{ __('Valore Soglia (Punteggio massimo 5)') }}</strong><br>
                                            <strong>{{ __('CIA 1') }}</strong><br>
                                            <span>Numeratore: Totale pazienti assistiti in cure domiciliari con
                                                intensità
                                                assistenziale CIA
                                                1</span></br>
                                            <span>Denominatore: Popolazione residente</span></br>
                                            <span>Fattore di scala: (x 1.000) </span></br>
                                            <strong>{{ __('CIA 2') }}</strong><br>
                                            <span>Numeratore: Totale pazienti assistiti in cure domiciliari con
                                                intensità
                                                assistenziale CIA
                                                2</span></br>
                                            <span>Denominatore: Popolazione residente</span></br>
                                            <span>Fattore di scala: (x 1.000) </span></br>
                                            <strong>{{ __('CIA 3') }}</strong><br>
                                            <span>Numeratore: Totale pazienti assistiti in cure domiciliari con
                                                intensità
                                                assistenziale CIA
                                                1</span></br>
                                            <span>Denominatore: Popolazione residente</span></br>
                                            <span>Fattore di scala: (x 1.000) </span></br></br>
                                            <strong>Il punteggio finale dell'indicatore è dato dalla somma pesata dei
                                                punteggi delle 3
                                                componenti CIA1, CIA2 e CIA3, pesati rispettivamente con i valori 0,15,
                                                0,35,
                                                0,50.</strong></br>
                                        </div>
                                    </div>




                                    <div id="decessiTumoreCP">

                                        <hr id="separatore8" class="my-4">
                                        <h5 class="text-center text-secondary mb-3">
                                            {{ __('Numero deceduti per causa di tumore assistiti dalla Rete di cure palliative sul numero deceduti per causa di tumore') }}
                                        </h5>
                                        <div class="row">
                                            <div class="col-md-4" style="width: 100%; max-width: 300px; margin: auto;">
                                                <x-chartjs-component :chart="$dataView['decessiTumore']" />
                                            </div>
                                            <div
                                                class="col-md-8 d-flex flex-column align-items-center justify-content-center">
                                                <div id="data-table9" class="w-100 mb-3">
                                                    <table class="table table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th>Distretto</th>
                                                                <th>Numeratore</th>
                                                                <th>Denominatore</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($dataView['primoGrafico'] as $dati)
                                                                <tr>
                                                                    <td>{{ $dati->nome_struttura ?? 'null' }}</td>
                                                                    <td>{{ $dati->numeratore ?? 'null' }}</td>
                                                                    <td>{{ $dati->ob10_1 ?? 'null' }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div id="message9"
                                                    class="message bg-light p-3 rounded border border-primary text-center w-100">
                                                    Qui inserirò il punteggio
                                                </div>
                                            </div>
                                        </div>
                                        <br>
                                        <div class="legend p-3 border rounded">
                                            <strong>Valore Soglia (Punteggio massimo 3)</strong><br>
                                            <span>Numero deceduti per causa di tumore assistiti dalla Rete di cure
                                                palliative a domicilio o
                                                in hospice/numero deceduti per causa di tumore (Valore >
                                                35%)</span></br>
                                            <span>Numeratore: Σ Assistiti in hospice con assistenza conclusa con decesso
                                                (Motivo conclusione
                                                valorizzato con 6) e per i quali la Patologia responsabile sia
                                                valorizzata
                                                con ICD-9-CM
                                                compreso tra 140-208 + Σ Assistiti in cure palliative domiciliari con
                                                assistenza conclusa
                                                per decesso (Motivo conclusione valorizzato con 3) per i quali la
                                                Patologia
                                                responsabile sia
                                                valorizzata con ICD-9-CM compreso tra 140-208. </span></br>
                                            <span>Denominatore: Media dei dati ISTAT di mortalità per causa tumore degli
                                                ultimi 3 anni
                                                disponibili.</span></br>
                                        </div>
                                    </div>


                                </div>


                            </div>

                        </div>
                    </div>


                    <div id="assistenzaOspedalieraBox" class="row justify-content-center">
                        <div class="col-md-12">
                            <div class="card shadow-sm border-0">
                                <div class="card-header bg-primary text-white">
                                    <b>{{ __('AREA DELL\'ASSISTENZA OSPEDALIERA') }}</b>
                                    <br />
                                </div>
                                <div class="card-body">
                                    <div id="tumoreMammella">
                                        <h5 class="text-center text-secondary mb-3">
                                            {{ __('Quota di interventi per tumore maligno della mammella eseguiti in reparti con volume di attività superiore a 150 (con 10% tolleranza) interventi annui') }}
                                        </h5>
                                        <div class="row">
                                            <div class="col-md-4" style="width: 100%; max-width: 300px; margin: auto;">
                                                <x-chartjs-component :chart="$dataView['mammellaTumore']" />
                                            </div>


                                            <div
                                                class="col-md-8 d-flex flex-column align-items-center justify-content-center">
                                                <table class="table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>Distretto</th>
                                                            <th>Numeratore</th>
                                                            <th>Denominatore</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($dataView['primoGrafico'] as $dati)
                                                            <tr>
                                                                <td>{{ $dati->nome_struttura ?? 'null' }}</td>
                                                                <td>{{ $dati->ob10_1 ?? 'null' }}</td>
                                                                <td>{{467}}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                                <div id="message10"
                                                    class="message bg-light p-3 rounded border border-primary text-center w-100">
                                                    Qui inserirò il messaggio
                                                </div>
                                            </div>
                                        </div>
                                        <br>
                                        <div class="legend p-3 border rounded">
                                            <strong>Valore Soglia (Punteggio massimo 2)</strong><br>
                                            <span>Proporzione di interventi per tumore maligno della mammella eseguiti
                                                in
                                                reparti con volume
                                                di attività superiore a 150 (con 10% tolleranza) annui.</span></br>
                                            <span>Numeratore: Numero di interventi chirurgici per tumore maligno della
                                                mammella in regime
                                                ordinario o day hospital, avvenuti in strutture italiane nell’anno di
                                                riferimento, con
                                                diagnosi principale o secondaria di tumore maligno della mammella
                                                (ICD-9-CM
                                                174, 198.81,
                                                233.0) ed intervento principale o secondario di quadrantectomia della
                                                mammella o mastectomia
                                                (ICD-9-CM 85.2x, 85.33, 85.34, 85.35, 85.36, 85.4.x) eseguiti in reparti
                                                con
                                                volume di
                                                attività superiore a 135 interventi annui. </span></br>
                                            <span>Denominatore: Numero di interventi chirurgici per tumore maligno della
                                                mammella in regime
                                                ordinario o day hospital, avvenuti in strutture italiane nell’anno di
                                                riferimento, con
                                                diagnosi principale o secondaria di tumore maligno della mammella
                                                (ICD-9-CM
                                                174, 198.81,
                                                233.0) ed intervento principale o secondario di quadrantectomia della
                                                mammella o mastectomia
                                                (ICD-9-CM 85.2x, 85.33, 85.34, 85.35, 85.36, 85.4.x). </span></br>
                                            <span>Fattore di scala: (x 100)</span></br>
                                        </div>
                                    </div>

                                    <hr id="separatore10" class="my-4">

                                    <!-- Rapporto tra ricoveri attribuiti a DRG ad alto rischio di inappropriatezza -->
                                    <div id="DRG">
                                        <h5 class="text-center text-secondary mb-3">
                                            {{ __('Rapporto tra ricoveri attribuiti a DRG ad alto rischio di inappropriatezza e ricoveri attribuiti a DRG non a rischio di inappropriatezza in regime ordinario') }}
                                        </h5>
                                        <div class="row">
                                            <div class="col-md-8">
                                                <x-chartjs-component :chart="$dataView['chartDRG']" />
                                            </div>
                                            <div
                                                class="col-md-4 d-flex flex-column align-items-center justify-content-center">
                                                <div id="message11"
                                                    class="message bg-light p-3 rounded border border-primary text-center w-100">
                                                    qui inserirò il messaggio
                                                </div>
                                                <div class="legend p-3 border rounded">
                                                    <strong>{{ __('Valore Soglia (Punteggio massimo 2)') }}</strong><br>
                                                    <ul style="list-style-type: none; padding: 0;">
                                                        <li><span
                                                                style="color: red; font-weight: bold;">&mdash;</span>{{ __('Soglia oltre la quale l\'obiettivo viene considerato non raggiunto') }}
                                                        </li>
                                                        <li><span
                                                                style="color: orange; font-weight: bold;"><b>&mdash;</b></span>{{ __('Soglia oltre la quale l\'obiettivo viene considerato parzialmente raggiunto') }}
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <hr id="separatore11" class="my-4">


                                    <div id="infezioniPostChirurgiche">
                                        <h5 class="text-center text-secondary mb-3">
                                            {{ __('Frequenza di infezioni post-chirurgiche') }}
                                        </h5>
                                        <div class="row">
                                            <div class="col-md-8">
                                                <x-chartjs-component
                                                    :chart="$dataView['chartInfezioniPostChirurgiche']" />
                                            </div>
                                            <div
                                                class="col-md-4 d-flex flex-column align-items-center justify-content-center">
                                                <div class="legend p-3 border rounded">
                                                    <strong>{{ __('Valore Soglia (Punteggio massimo 4)') }}</strong><br>
                                                    <span>{{ __('Verso dell\'indicatore: decrescente. Al diminuire del valore dell\'indicatore aumenta la garanzia del LEA.') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="message11"
                                        class="message bg-light p-3 rounded border border-primary text-center w-100">
                                        qui inserirò il messaggio del punteggio complessivo
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