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
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <table class="table table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th>Struttura</th>
                                                                <th>Sub 1.1</th>
                                                                <th>Sub 1.2</th>
                                                                <th>Sub 1.3</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                        @foreach($dataView['files'] as $struttura => $categorie)
                                                            @foreach($categorie as $categoria => $caricato)
                                                                @if($categoria == config("constants.TARGET_CATEGORY.OB6_ISTITUZIONE_COORDINAMENTO"))
                                                                    @php
                                                                        $sub1_1 = $caricato;
                                                                    @endphp
                                                                @elseif($categoria == config("constants.TARGET_CATEGORY.OB6_ASSEGNAZIONE_INCARICO"))
                                                                    @php
                                                                        $sub1_2 = $caricato;
                                                                    @endphp
                                                                @elseif($categoria == config("constants.TARGET_CATEGORY.OB6_INDIVIDUAZIONE_INFERMIERE"))
                                                                    @php
                                                                        $sub1_3 = $caricato;
                                                                    @endphp
                                                                @endif
                                                            @endforeach
                                                            <tr>
                                                                <td>{{ $struttura }} </td>                                            
                                                                <td class="text-white text-center" style="background-color:{{$sub1_1 == 0 ? "red" : "green"}};">{{ $sub1_1 == 0 ? "No" : "Si" }} </td>
                                                                <td class="text-white text-center" style="background-color:{{$sub1_2 == 0 ? "red" : "green"}};">{{ $sub1_2 == 0 ? "No" : "Si" }} </td>
                                                                <td class="text-white text-center" style="background-color:{{$sub1_3 == 0 ? "red" : "green"}};">{{ $sub1_3 == 0 ? "No" : "Si" }} </td>
                                                            </tr>
                                                        @endforeach
                                                        </tbody>
                                                        <caption>
                                                            <div class="it-list-wrapper"><ul class="it-list">
                                                                <li>Sub 1.1: Istituzione Coordinamento Locale Aziendale;</li>
                                                                <li>Sub 1.2: Assegnazione dell’incarico di altissima specialit&agrave; per il coordinatore locale Aziendale;</li>
                                                                <li>Sub 1.3: Individuazione di un infermiere dedicato al procurement.</li>
                                                            </ul></div>
                                                        </caption>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-header bg-primary text-white">
                            <b>{{ __('Sub-Obiettivo 2: Numero  di  accertamenti  di  morte  con  criterio  neurologico/numero  di
                                decessi aziendali per grave neurolesione') }}</b>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 d-flex align-items-center justify-content-center">
                                    <x-chartjs-component :chart="$dataView['chartDonazioni']" />
                                </div>
                            </div>
                            <div class="row">
                                <div id="container" class="col-md-12">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Struttura</th>
                                                <th>Anno</th>
                                                <th>Totale accertamenti</th>
                                                <th>Incr rispetto al 2023</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(isset($dataView['result']))
                                                @foreach ($dataView['result'] as $res)
                                                    <tr>
                                                        <td>{{ $res['nome_struttura'] }}</td> 
                                                        <td>{{ $res['anno'] }}</td> 
                                                        <td>{{ $res['totale_accertamenti'] }}</td> 
                                                        <td>{{ $res['incrementoAccertamenti'] }} %</td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
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
                                                <strong>Scala valori di riferimento per l'anno 2024</strong><br>
                                                <span>Se il valore dell'indicatore &egrave; maggiore del 5% l'obiettivo &egrave; pienamente raggiunto</span><br />
                                                <span>Se il valore dell'indicatore &egrave; compreso tra il 3% e il 5% l'obiettivo &egrave; raggiunto all'80%.</span><br />
                                                <span>Se il valore dell'indicatore &egrave; compreso tra il 5% e il 2% l'obiettivo &egrave; raggiunto al 50%.</span><br />
                                                <span>Se il valore dell'indicatore &egrave; minore del 2% l'obiettivo non &egrave; raggiunto.</span><br />
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
                                                    <strong>Scala valori di riferimento per l'anno 2025</strong><br>
                                                    <span>Se il valore dell'indicatore &egrave; maggiore o pari del 15% l'obiettivo &egrave; pienamente raggiunto.</span><br />
                                                    <span>Se il valore dell'indicatore &egrave; inferiore al 15% l'obiettivo &egrave; raggiunto all'80%.</span><br />
                                                    <span>Se il valore dell'indicatore &egrave; inferiore al 10% l'obiettivo &egrave; raggiunto al 50%.</span><br />
                                                    <span>Se il valore dell'indicatore &egrave; minore del 7% l'obiettivo non &egrave; raggiunto.</span><br />
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
                                                <strong>Scala valori di riferimento per l'anno 2026</strong><br>
                                                <span>Se il valore dell'indicatore &egrave; maggiore o pari del 25% l'obiettivo &egrave; pienamente raggiunto.</span><br />
                                                <span>Se il valore dell'indicatore &egrave; inferiore al 25% l'obiettivo &egrave; raggiunto all'80%.</span><br />
                                                <span>Se il valore dell'indicatore &egrave; inferiore al 15% l'obiettivo &egrave; raggiunto al 50%.</span><br />
                                                <span>Se il valore dell'indicatore &egrave; minore del 10% l'obiettivo non &egrave; raggiunto.</span><br />
                                            </div>
                                       </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-header bg-primary text-white mt-4">
                            <b>{{ __('Sub-Obiettivo 3: Tasso di opposizione alla donazione non superiore al 38%') }}</b>
                        </div>
                        
                        <div class="card-body">
                            <div class="container">
                                <form action="{{ route('donazioni') }}" method="GET" enctype="multipart/form-data">
                                    <div class="row align-items-end">
                                        <div class="col-md-6">
                                            <div class="form-group d-flex">
                                                <select id="annoSelezionato" class="form-control mr-2" name="annoSelezionato">
                                            @for ($anno = date('Y'); $anno >= 2023; $anno--)
                                                        <option value="{{ $anno }}" {{ $dataView['annoSelezionato'] == $anno ? "selected" : "" }}>{{ $anno }}</option>
                                                    @endfor
                                                </select>
                                                <button type="submit" class="btn btn-primary">Invia</button>
                                            </div>
                                        </div>
                                        <input type="hidden" name="annoCorrente" value="{{ date('Y') }}">
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
                                                <th>Struttura</th>
                                                <th>N.Opposti</th>
                                                <th>Numero accertamenti</th>
                                                <th>Percentuale</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($dataView['result'] as $res)
                                                @if ($res['anno'] == $dataView['annoSelezionato'])
                                                <tr>
                                                    <td>{{ $res['nome_struttura'] }}</td> 
                                                    <td>{{ $res['numero_opposti'] }}</td>
                                                    <td>{{ $res['totale_accertamenti'] }}</td>
                                                    <td>{{ $res['percentualeOpposizioni'] }} %</td>
                                                </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="legend mt-3 border rounded p-3">
                                <strong>Scala valori di riferimento per l'anno in corso</strong><br>
                                <span>Se il valore dell'indicatore non &egrave; maggiore del 38% l'obiettivo &egrave; pienamente raggiunto.</span><br />
                                <span>Se il valore dell'indicatore &egrave; compreso tra il 38% e il 41% l'obiettivo &egrave; raggiunto all'80%.</span><br />
                                <span>Se il valore dell'indicatore &egrave; compreso tra il 41% e il 45% l'obiettivo &egrave; raggiunto al 50%.</span><br />
                                <span>Se il valore dell'indicatore &egrave; minore del 45% l'obiettivo non &egrave; raggiunto.</span><br />
                            </div>
                        </div>
                    </div>
                    <div class="card shadow-sm border-0 mb-3">
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
                                    <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Struttura</th>
                                            <th>Anno</th>
                                            <th>Totale cornee</th>
                                            <th>Incr. rispetto al 2023</th>
                                
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($dataView['result'] as $res)
                                            <tr>
                                                <td>{{ $res['nome_struttura'] }}</td> 
                                                <td>{{ $res['anno'] }}</td> 
                                                <td>{{ $res['totale_cornee']}}</td> 
                                                <td>{{ $res['incrementoCornee']}} %</td> 
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    </table>
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
                                            <span>Se l'incremento &egrave; superiore al 10% l'obiettivo &egrave; pienamente raggiunto (1 punti).</span><br />
                                            <span>Se l'incremento &egrave; inferiore al 10% ma pari o superiore al 5% l'obiettivo &egrave; raggiunto all'80% (0.8 punti).</span><br />
                                            <span>Se l'incremento &egrave; inferiore al 5% ma pari o superiore al 3% l'obiettivo &egrave; raggiunto al 50% (0.5 punti).</span><br />
                                            <span>Se l'incremento &egrave; inferiore al 3% ma pari o superiore al 2% l'obiettivo non &egrave; raggiunto (0 punti).</span><br />
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
                                                <span>Se il valore dell'indicatore &egrave; maggiore o pari del 15% l'obiettivo &egrave; pienamente raggiunto (1.5 punti).</span><br />
                                                <span>Se il valore dell'indicatore &egrave; inferiore al 15% l'obiettivo &egrave; raggiunto all'80% (1.2 punti).</span><br />
                                                <span>Se il valore dell'indicatore &egrave; inferiore al 10% l'obiettivo &egrave; raggiunto al 50% (0.75 punti).</span><br />
                                                <span>Se il valore dell'indicatore &egrave; minore del 7% l'obiettivo non &egrave; raggiunto (0 punti).</span><br />
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
                                            <span>Se il valore dell'indicatore &egrave; maggiore o pari del 30% l'obiettivo &egrave; pienamente raggiunto (1.5 punti).</span><br />
                                            <span>Se il valore dell'indicatore &egrave; inferiore al 25% l'obiettivo &egrave; raggiunto all'80% (1.2 punti).</span><br />
                                            <span>Se il valore dell'indicatore &egrave; inferiore al 20% l'obiettivo &egrave; raggiunto al 50% (0.75 punti).</span><br />
                                            <span>Se il valore dell'indicatore &egrave; minore del 15% l'obiettivo non &egrave; raggiunto (0 punti).</span><br />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card shadow-sm border-0 mb-3">
                            
                        <div class="card-header bg-primary text-white">
                            <b>{{ __('Sub-Obiettivo 5: Organizzazione in ambito aziendale di almeno due corsi di formazione
                                e/o sensibilizzazione.') }}</b>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-striped">
                                        <thead>
                                        <tr>
                                            <th>Struttura</th>
                                            <th>Risultato</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($dataView['files'] as $struttura => $categorie)
                                            @foreach($categorie as $categoria => $caricato)
                                                @if($categoria == config("constants.TARGET_CATEGORY.OB6_ORGANIZZAZIONE_CORSI"))
                                                <tr>
                                                    <td>{{ $struttura }} </td>                                            
                                                    <td class="text-white text-center" style="background-color:{{$caricato == 0 ? "red" : "green"}};">{{ $caricato == 0 ? "No" : "Si" }} </td>
                                                </tr>
                                                @endif
                                            @endforeach
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="legend p-3 border rounded">
                            <strong>Scala valori di riferimento</strong><br>
                            <span>Se il valore dell'indicatore &egrave; superato, l'obiettivo &egrave; pienamente raggiunto.</span><br />
                            <span>Se il valore dell'indicatore non &egrave; superato, l'obiettivo &egrave; non raggiunto.</span><br />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection