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
                        <i class="fas fa-hand-holding-medical"></i> <!-- Icona corrispondente all'obiettivo -->
                        {{ __('Obiettivo 6: Donazioni') }} <!-- Testo del titolo -->
                    </h4>
                    <small>{{ __('') }}</small> <!-- Descrizione -->
                </div>
                <div class="card-body">
                    <div class="card shadow-sm border-0 mb-3">
                            <div class="card-header bg-primary text-white">
                                <b>{{ __('Sub-Obiettivo 1: Istituzione  del  Coordinamento  Locale  Aziendale  per  il  Procurement Organi e Tessuti con assegnazione dell’incarico di altissima specialità
                            per il Coordinatore locale Aziendale e l’individuazione di un infermiere
                            dedicato al procurement') }}</b>
                            </div>

                            <div class="card-body">
                                
                            <div id="istituzione_coordinamento" class="row justify-content-center mt-3">
                                    <div class="col-md-12">
                                        <div class="card shadow-sm border-0">
                                            <div class="card-header bg-light text-dark">
                                                <b>{{ __('Istituzione Coordinamento Locale Aziendale') }}</b>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    @if(isset($dataView['file']))
                                                        @php
                                                        $categoria_trovata = false;
                                                        @endphp
                                                        @foreach( $dataView['file']  as $categoria_risultato)
                                                            @if($categoria_risultato->category === 'Istituzione Coordinamento Locale Aziendale')
                                                                @php
                                                                $categoria_trovata = true;
                                                                @endphp

                                                                @if($categoria_risultato->validator_user_id === null)
                                                                    <div id="message5" class="message bg-light p-3 rounded border border-primary text-center w-100" style="color: orange;">
                                                                        <strong>File caricato il: {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $categoria_risultato->created_at)->format('d/m/Y H:i') }} in attesa di approvazione.</strong>
                                                                    </div>
                                                                    @else
                                                                    @if($categoria_risultato->approved === 1)
                                                                    <div id="message5" class="message bg-light p-3 rounded border border-primary text-center w-100" style="color:green;">
                                                                        <strong>Il file caricato il: {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $categoria_risultato->created_at)->format('d/m/Y H:i') }} è stato approvato -> Obiettivo raggiunto!</strong>
                                                                    </div>
                                                                    @else
                                                                    <div id="message5" class="message bg-light p-3 rounded border border-primary text-center w-100" style="color:red;">
                                                                        <strong>Il file caricato il: {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $categoria_risultato->created_at)->format('d/m/Y H:i') }} non è stato approvato -> Obiettivo non raggiunto!</strong>
                                                                    </div>
                                                                    @endif
                                                                @endif
                                                            @endif
                                                        @endforeach

                                                        @if(!$categoria_trovata)
                                                        <div id="message5" class="message bg-light p-3 rounded border border-primary text-center w-100" style="color:red;">
                                                            <strong>Il file non è ancora stato caricato -> Obiettivo non raggiunto!</strong>
                                                        </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            
                                <!-- Assegnazione dell’incarico di altissima specialità per il coordinatore locale Aziendale -->
                            
                            
                                <div id="assegnazione_incarico" class="row justify-content-center mt-3">
                                    <div class="col-md-12">
                                        <div class="card shadow-sm border-0">
                                            <div class="card-header bg-light text-dark">
                                                <b>{{ __('Assegnazione dell’incarico di altissima specialità per il coordinatore locale Aziendale') }}</b>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    @if(isset($dataView['file']))
                                                        @php
                                                        $categoria_trovata = false;
                                                        @endphp

                                                        @foreach($dataView['file'] as $categoria_risultato)
                                                            @if($categoria_risultato->category === 'Assegnazione dell’incarico di altissima specialità per il coordinatore locale Aziendale')
                                                                @php
                                                                $categoria_trovata = true;
                                                                @endphp

                                                                @if($categoria_risultato->validator_user_id === null)
                                                                    <div id="message5" class="message bg-light p-3 rounded border border-primary text-center w-100" style="color: orange;">
                                                                        <strong>File caricato il: {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $categoria_risultato->created_at)->format('d/m/Y H:i') }} in attesa di approvazione.</strong>
                                                                    </div>
                                                                    @else
                                                                    @if($categoria_risultato->approved === 1)
                                                                    <div id="message5" class="message bg-light p-3 rounded border border-primary text-center w-100" style="color:green;">
                                                                        <strong>Il file caricato il: {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $categoria_risultato->created_at)->format('d/m/Y H:i') }} è stato approvato -> Obiettivo raggiunto!</strong>
                                                                    </div>
                                                                    @else
                                                                    <div id="message5" class="message bg-light p-3 rounded border border-primary text-center w-100" style="color:red;">
                                                                        <strong>Il file caricato il: {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $categoria_risultato->created_at)->format('d/m/Y H:i') }} non è stato approvato -> Obiettivo non raggiunto!</strong>
                                                                    </div>
                                                                    @endif
                                                                @endif
                                                            @endif
                                                        @endforeach

                                                        @if(!$categoria_trovata)
                                                        <div id="message5" class="message bg-light p-3 rounded border border-primary text-center w-100" style="color:red;">
                                                            <strong>Il file non è ancora stato caricato -> Obiettivo non raggiunto!</strong>
                                                        </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!--Individuazione di un infermiere dedicato al procurement-->

                                <div id="infermiere_dedicato" class="row justify-content-center mt-3">
                                    <div class="col-md-12">
                                        <div class="card shadow-sm border-0">
                                            <div class="card-header bg-light text-dark">
                                                <b>{{ __('Individuazione di un infermiere dedicato al procurement') }}</b>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    @if(isset( $dataView['file']))
                                                        @php
                                                        $categoria_trovata = false; // Variabile per tracciare se la categoria è stata trovata
                                                        @endphp

                                                        @foreach($dataView['file'] as $categoria_risultato)
                                                            @if($categoria_risultato->category === 'Individuazione di un infermiere dedicato al procurement')
                                                                @php
                                                                $categoria_trovata = true; // Categoria trovata
                                                                @endphp

                                                                @if($categoria_risultato->validator_user_id === null)
                                                                    <div id="message5" class="message bg-light p-3 rounded border border-primary text-center w-100" style="color: orange;">
                                                                        <strong>File caricato il: {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $categoria_risultato->created_at)->format('d/m/Y H:i') }} in attesa di approvazione.</strong>
                                                                    </div>
                                                                    @else
                                                                    @if($categoria_risultato->approved === 1)
                                                                    <div id="message5" class="message bg-light p-3 rounded border border-primary text-center w-100" style="color:green;">
                                                                        <strong>Il file caricato il: {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $categoria_risultato->created_at)->format('d/m/Y H:i') }} è stato approvato -> Obiettivo raggiunto!</strong>
                                                                    </div>
                                                                    @else
                                                                    <div id="message5" class="message bg-light p-3 rounded border border-primary text-center w-100" style="color:red;">
                                                                        <strong>Il file caricato il: {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $categoria_risultato->created_at)->format('d/m/Y H:i') }} non è stato approvato -> Obiettivo non raggiunto!</strong>
                                                                    </div>
                                                                    @endif
                                                                @endif
                                                            @endif                  
                                                        @endforeach

                                                        @if(!$categoria_trovata)
                                                        <div id="message5" class="message bg-light p-3 rounded border border-primary text-center w-100" style="color:red;">
                                                            <strong>Il file non è ancora stato caricato -> Obiettivo non raggiunto!</strong>
                                                        </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="legend p-3 border rounded">
                                    <strong>Scala valori di riferimento (Punteggio massimo 0.5)</strong><br>
                                    <span>Se il valore di tutti e 3 gli indicatori &egrave; superato, l'obiettivo è pienamente raggiunto (0.5 punti).</span><br />
                                    <span>Se il valore di un indicatore è non &egrave; superato, l'obiettivo è non raggiunto (0 punti).</span><br />
                                </div>
                            </div>
                        <div class="card-header bg-primary text-white">
                            <b>{{ __('Sub-Obiettivo 2: Numero  di  accertamenti  di  morte  con  criterio  neurologico/numero  di
                                decessi aziendali per grave neurolesione') }}</b>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-5 d-flex align-items-center justify-content-center">
                                    <x-chartjs-component :chart="$dataView['chartDonazioni']" />
                                </div>
                                <div id="container" class="col-md-7">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Anno</th>
                                                <th>Totale accertamenti</th>
                                                <th>Incr rispetto al 2023</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(isset($dataView['result']))
                                                @foreach ($dataView['result'] as $res)
                                                    <tr>
                                                        <td>{{ $res['anno'] }}</td> 
                                                        <td>{{ $res['totale_accertamenti'] }}</td> 
                                                        <td>{{ $res['incrementoAccertamenti'] }} %</td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>

                                    <div class="legend p-3 border rounded mt-3 {{ $dataView['messaggioTmp']['class'] }}">
                                        <strong>{{ $dataView['messaggioTmp']['text'] }}</strong>
                                    </div>
                                </div>
                            </div>
                            <br>
                            <div class="accordion accordion-flush" id="accordionFlushExample">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="flush-headingOne">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
                                        2024
                                    </button>
                                    </h2>
                                    <div id="flush-collapseOne" class="accordion-collapse collapse" aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample">
                                        <div class="accordion-body">
                                            <div class="legend mt-3 border rounded p-3">
                                                <strong>Scala valori di riferimento per l'anno 2024 (Punteggio massimo 1.5)</strong><br>
                                                <span>Se il valore dell'indicatore è maggiore del 5% l'obiettivo è pienamente raggiunto (1.5 punti).</span><br />
                                                <span>Se il valore dell'indicatore è compreso tra il 3% e il 5% l'obiettivo è raggiunto all'80% (1.2 punti).</span><br />
                                                <span>Se il valore dell'indicatore è compreso tra il 5% e il 2% l'obiettivo è raggiunto al 50% (0.75 punti).</span><br />
                                                <span>Se il valore dell'indicatore è minore del 2% l'obiettivo non è raggiunto (0 punti).</span><br />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="flush-headingTwo">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseTwo" aria-expanded="false" aria-controls="flush-collapseTwo">
                                        2025
                                    </button>
                                    </h2>
                                    <div id="flush-collapseTwo" class="accordion-collapse collapse" aria-labelledby="flush-headingTwo" data-bs-parent="#accordionFlushExample">
                                        <div class="accordion-body">
                                            <div class="accordion-body">
                                                <div class="legend mt-3 border rounded p-3">
                                                    <strong>Scala valori di riferimento per l'anno 2025 (Punteggio massimo 1.5)</strong><br>
                                                    <span>Se il valore dell'indicatore è maggiore o pari del 15% l'obiettivo è pienamente raggiunto (1.5 punti).</span><br />
                                                    <span>Se il valore dell'indicatore è inferiore al 15% l'obiettivo è raggiunto all'80% (1.2 punti).</span><br />
                                                    <span>Se il valore dell'indicatore è inferiore al 10% l'obiettivo è raggiunto al 50% (0.75 punti).</span><br />
                                                    <span>Se il valore dell'indicatore è minore del 7% l'obiettivo non è raggiunto (0 punti).</span><br />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="flush-headingThree">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseThree" aria-expanded="false" aria-controls="flush-collapseThree">
                                       2026
                                    </button>
                                    </h2>
                                    <div id="flush-collapseThree" class="accordion-collapse collapse" aria-labelledby="flush-headingThree" data-bs-parent="#accordionFlushExample">
                                        <div class="accordion-body">
                                            <div class="legend mt-3 border rounded p-3">
                                                <strong>Scala valori di riferimento per l'anno 2026 (Punteggio massimo 1.5)</strong><br>
                                                <span>Se il valore dell'indicatore è maggiore o pari del 25% l'obiettivo è pienamente raggiunto (1.5 punti).</span><br />
                                                <span>Se il valore dell'indicatore è inferiore al 25% l'obiettivo è raggiunto all'80% (1.2 punti).</span><br />
                                                <span>Se il valore dell'indicatore è inferiore al 15% l'obiettivo è raggiunto al 50% (0.75 punti).</span><br />
                                                <span>Se il valore dell'indicatore è minore del 10% l'obiettivo non è raggiunto (0 punti).</span><br />
                                            </div>
                                       </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-header bg-primary text-white">
                            <b>{{ __('Sub-Obiettivo 3: Tasso di opposizione alla donazione non superiore al 38%') }}</b>
                        </div>
                        
                                

                    <div class="card-body">
                        <div class="container">
                            <form action="{{ route('donazioni') }}" method="GET" enctype="multipart/form-data">
                                <div class="row align-items-end">
                                    <div class="col-md-6">
                                        <div class="form-group d-flex">
                                            <select id="annoSelezionato" class="form-control mr-2" name="annoSelezionato">
                                                @php
                                                $annoCorrente = date('Y');
                                                $annoMinimo = 2023; 
                                                for ($anno = $annoCorrente; $anno >= $annoMinimo; $anno--) {
                                                    echo "<option value=\"$anno\">$anno</option>";
                                                }
                                                @endphp
                                            </select>
                                            <button type="submit" class="btn btn-primary">Invia</button>
                                        </div>
                                    </div>
                                    <input type="hidden" name="annoCorrente" value="{{ $annoCorrente }}">
                                </div>
                            </form>
                        </div>

                        <div class="row">
                            <div style="width: 100%; max-width: 400px; margin: auto;">
                                <x-chartjs-component :chart="$dataView['chartSubObiettivo3']" />
                            </div>
                            <div id="container" class="col-md-7">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>N.Opposti</th>
                                            <th>Numero accertamenti</th>
                                            <th>Percentuale</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    
                                        <tr>
                                            <td>{{ $dataView['numeratoreSecondo'] }}</td>
                                            <td>{{ $dataView['denominatoreSecondo'] }}</td>
                                            <td>{{ number_format($dataView['percentualeOpposizione'], 2) . '%' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="legend p-3 border rounded mt-3 {{ $dataView['messaggiOb6_3']['class'] }}">
                                <strong>{{ $dataView['messaggiOb6_3']['text'] }}</strong>
                            </div>
                            </div>
                        </div>
                        <div class="legend mt-3 border rounded p-3">
                            <strong>Scala valori di riferimento per l'anno in corso (Punteggio massimo 1.5)</strong><br>
                            <span>Se il valore dell'indicatore non è maggiore del 38% l'obiettivo è pienamente raggiunto (1.5 punti).</span><br />
                            <span>Se il valore dell'indicatore è compreso tra il 38% e il 41% l'obiettivo è raggiunto all'80% (1.2 punti).</span><br />
                            <span>Se il valore dell'indicatore è compreso tra il 41% e il 45% l'obiettivo è raggiunto al 50% (0.75 punti).</span><br />
                            <span>Se il valore dell'indicatore è minore del 45% l'obiettivo non è raggiunto (0 punti).</span><br />
                        </div>
                    </div>


                    <div class="card-header bg-primary text-white">
                        <b>{{ __('Sub-Obiettivo 4: Incremento  del  procurement  di  cornee  in  toto  (da  cadavere  a  cuore
                            fermo e a cuore barrente) non inferiore al 38%') }}</b>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-5 d-flex align-items-center justify-content-center">
                                <x-chartjs-component :chart="$dataView['chartSubObiettivo4']" />
                            </div>
                            <div id="container" class="col-md-7">
                                <table class="table table-striped">
                                <table class="table">
                                <thead>
                                    <tr>
                                        <th>Anno</th>
                                        <th>Totale cornee</th>
                                        <th>Incr. rispetto al 2023</th>
                            
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($dataView['result'] as $res)
                                        <tr>
                                            <td>{{ $res['anno'] }}</td> 
                                            <td>{{ $res['totale_cornee']}}</td> 
                                            <td>{{ $res['incrementoCornee']}} %</td> 
                                        </tr>
                                    @endforeach  
                                </tbody>
                            </table>

                            <div class="legend p-3 border rounded mt-3 {{ $dataView['messaggioTmpIncremento']['classIncremento'] }}">
                                <strong>{{ $dataView['messaggioTmpIncremento']['textIncremento'] }}</strong>
                            </div>
                            </div>
                        </div>
                        <br>
                        <div class="accordion accordion-flush" id="accordionFlushExample">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="flush-headingOne">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
                                    2024
                                </button>
                                </h2>
                                <div id="flush-collapseOne" class="accordion-collapse collapse" aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample">
                                    <div class="accordion-body">
                                        <div class="legend mt-3 border rounded p-3">
                                            <strong>Scala valori di riferimento per l'anno 2024 (Punteggio massimo 1)</strong><br>
                                            <span>Se l'incremento è superiore al 10% l'obiettivo è pienamente raggiunto (1 punti).</span><br />
                                            <span>Se l'incremento è inferiore al 10% ma pari o superiore al 5% l'obiettivo è raggiunto all'80% (0.8 punti).</span><br />
                                            <span>Se l'incremento è inferiore al 5% ma pari o superiore al 3% l'obiettivo è raggiunto al 50% (0.5 punti).</span><br />
                                            <span>Se l'incremento è inferiore al 3% ma pari o superiore al 2% l'obiettivo non è raggiunto (0 punti).</span><br />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="flush-headingTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseTwo" aria-expanded="false" aria-controls="flush-collapseTwo">
                                    2025
                                </button>
                                </h2>
                                <div id="flush-collapseTwo" class="accordion-collapse collapse" aria-labelledby="flush-headingTwo" data-bs-parent="#accordionFlushExample">
                                    <div class="accordion-body">
                                        <div class="accordion-body">
                                            <div class="legend mt-3 border rounded p-3">
                                                <strong>Scala valori di riferimento per l'anno 2025 (Punteggio massimo 1)</strong><br>
                                                <span>Se il valore dell'indicatore è maggiore o pari del 15% l'obiettivo è pienamente raggiunto (1.5 punti).</span><br />
                                                <span>Se il valore dell'indicatore è inferiore al 15% l'obiettivo è raggiunto all'80% (1.2 punti).</span><br />
                                                <span>Se il valore dell'indicatore è inferiore al 10% l'obiettivo è raggiunto al 50% (0.75 punti).</span><br />
                                                <span>Se il valore dell'indicatore è minore del 7% l'obiettivo non è raggiunto (0 punti).</span><br />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="flush-headingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseThree" aria-expanded="false" aria-controls="flush-collapseThree">
                                2026
                                </button>
                                </h2>
                                <div id="flush-collapseThree" class="accordion-collapse collapse" aria-labelledby="flush-headingThree" data-bs-parent="#accordionFlushExample">
                                    <div class="accordion-body">
                                        <div class="legend mt-3 border rounded p-3">
                                            <strong>Scala valori di riferimento per l'anno 2026 (Punteggio massimo 1)</strong><br>
                                            <span>Se il valore dell'indicatore è maggiore o pari del 30% l'obiettivo è pienamente raggiunto (1.5 punti).</span><br />
                                            <span>Se il valore dell'indicatore è inferiore al 25% l'obiettivo è raggiunto all'80% (1.2 punti).</span><br />
                                            <span>Se il valore dell'indicatore è inferiore al 20% l'obiettivo è raggiunto al 50% (0.75 punti).</span><br />
                                            <span>Se il valore dell'indicatore è minore del 15% l'obiettivo non è raggiunto (0 punti).</span><br />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

              
                           
                    <div class="card-header bg-primary text-white">
                        <b>{{ __('Sub-Obiettivo 5: Organizzazione in ambito aziendale di almeno due corsi di formazione
                            e/o sensibilizzazione.') }}</b>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @if(isset($dataView['file']))
                                @php
                                $categoria_trovata = false;
                                @endphp
                                    @foreach($dataView['file']  as $categoria_risultato)
                                        @if($categoria_risultato->category === 'Organizzazione di almeno 2 Corsi di formazione e/o sensibilizzazione')
                                            @php
                                            $categoria_trovata = true;
                                            @endphp

                                            @if($categoria_risultato->validator_user_id === null)
                                                <div id="message5" class="message bg-light p-3 rounded border border-primary text-center w-100" style="color: orange;">
                                                    <strong>File caricato il: {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $categoria_risultato->created_at)->format('d/m/Y H:i') }} in attesa di approvazione.</strong>
                                                </div>
                                                @else
                                                    @if($categoria_risultato->approved === 1)
                                                        <div id="message5" class="message bg-light p-3 rounded border border-primary text-center w-100" style="color:green;">
                                                            <strong>Il file caricato il: {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $categoria_risultato->created_at)->format('d/m/Y H:i') }} è stato approvato -> Obiettivo raggiunto!</strong>
                                                        </div>
                                                    @else
                                                    <div id="message5" class="message bg-light p-3 rounded border border-primary text-center w-100" style="color:red;">
                                                        <strong>Il file caricato il: {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $categoria_risultato->created_at)->format('d/m/Y H:i') }} non è stato approvato -> Obiettivo non raggiunto!</strong>
                                                    </div>
                                            @endif
                                        @endif
                                    @endif
                                @endforeach

                                @if(!$categoria_trovata)
                                <div id="message5" class="message bg-light p-3 rounded border border-primary text-center w-100" style="color:red;">
                                    <strong>Il file non è ancora stato caricato -> Obiettivo non raggiunto!</strong>
                                </div>
                                @endif
                            @endif
                        </div>
                        <div class="legend p-3 border rounded">
                            <strong>Scala valori di riferimento (Punteggio massimo 0.5)</strong><br>
                            <span>Se il valore dell'indicatore &egrave; superato, l'obiettivo è pienamente raggiunto (0.5 punti).</span><br />
                            <span>Se il valore dell'indicatore non &egrave; superato, l'obiettivo è non raggiunto (0 punti).</span><br />
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection