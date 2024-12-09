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

                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-primary text-white">
                            <b>{{ __('Indicatori di risultato') }}</b>
                        </div>

                        <div class="card-body">
                            <div class="row justify-content-center mt-4">
                                <div class="col-md-12">
                                    <div style="width: 100%; margin: auto;">
                                        <x-chartjs-component :chart="$dataView['lineChartMammografico']" />
                                    </div>
                                    <div style="width: 100%; margin: auto;">
                                        <table class="table">
                                            <tr>
                                                <th>&nbsp;</th>
                                                @for ($i = 1; $i <= 12; $i++)
                                                    <th>{{ array_search($i, config("constants.MESI")) }}</th>
                                                @endfor
                                            </tr>
                                        @foreach ($dataView['indicatoriRisultato'] as $idStruttura => $aree)
                                            <tr>
                                                <td>{{ $aree['name'] }} </td>                                            
                                                @foreach ($aree['mammografico'] as $percentuali)
                                                    <td class="text-white text-center" style="background-color:{{$dataView['colori'][$idStruttura]['mammografico'][$loop->index]}};">{{ $percentuali }} </td>                                            
                                                @endforeach
                                            </tr>
                                        @endforeach
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="legend p-3 border rounded">
                                    Se il valore dell'indicatore &egrave; minore del 35% l'obiettivo non &egrave; raggiunto;<br>
                                    Se il valore dell'indicatore &egrave; compreso tra il 35% e il 59% l'obiettivo &egrave; parzialmente raggiunto;<br>
                                    Se il valore dell'indicatore &egrave; maggiore del 60% l'obiettivo &egrave; raggiunto.
                                </div>
                            </div>
                        </div>
                            <div class="card-body">
                            <div class="row justify-content-center mt-4">
                                <div class="col-md-12">
                                    <div style="width: 100%; margin: auto;">
                                        <x-chartjs-component :chart="$dataView['lineChartCervicocarcinoma']" />
                                    </div>
                                    <div style="width: 100%; margin: auto;">
                                        <table class="table">
                                            <tr>
                                                <th>&nbsp;</th>
                                                @for ($i = 1; $i <= 12; $i++)
                                                    <th>{{ array_search($i, config("constants.MESI")) }}</th>
                                                @endfor
                                            </tr>
                                        @foreach ($dataView['indicatoriRisultato'] as $idStruttura => $aree)
                                            <tr>
                                                <td>{{ $aree['name'] }} </td>                                            
                                                @foreach ($aree['cervicocarcinoma'] as $percentuali)
                                                    <td class="text-white text-center" style="background-color:{{$dataView['colori'][$idStruttura]['cervicocarcinoma'][$loop->index]}};">{{ $percentuali }} </td>                                            
                                                @endforeach
                                            </tr>
                                        @endforeach
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="legend p-3 border rounded">
                                    Se il valore dell'indicatore &egrave; minore del 25% l'obiettivo non &egrave; raggiunto;<br>
                                    Se il valore dell'indicatore &egrave; compreso tra il 25% e il 49% l'obiettivo &egrave; parzialmente raggiunto;<br>
                                    Se il valore dell'indicatore &egrave; maggiore del 50% l'obiettivo &egrave; raggiunto.
                                </div>
                            </div>     
                        </div>
                        <div class="card-body">
                        
                            <div class="row justify-content-center mt-4">
                                <div class="col-md-12 ">
                                    <div style="width: 100%; margin: auto;">
                                        <x-chartjs-component :chart="$dataView['lineChartColonretto']" />
                                    </div>
                                    <div style="width: 100%; margin: auto;">
                                        <table class="table">
                                            <tr>
                                                <th>&nbsp;</th>
                                                @for ($i = 1; $i <= 12; $i++)
                                                    <th>{{ array_search($i, config("constants.MESI")) }}</th>
                                                @endfor
                                            </tr>
                                        @foreach ($dataView['indicatoriRisultato'] as $idStruttura => $aree)
                                            <tr>
                                                <td>{{ $aree['name'] }} </td>                                            
                                                @foreach ($aree['colonretto'] as $percentuali)
                                                    <td class="text-white text-center" style="background-color:{{$dataView['colori'][$idStruttura]['colonretto'][$loop->index]}};">{{ $percentuali }} </td>                                            
                                                @endforeach
                                            </tr>
                                        @endforeach
                                        </table>
                                    </div>                           
                                </div>                        
                            </div>
                            <div class="row">
                                <div class="legend p-3 border rounded">
                                    Se il valore dell'indicatore &egrave; minore del 25% l'obiettivo non &egrave; raggiunto;<br>
                                    Se il valore dell'indicatore &egrave; compreso tra il 25% e il 49% l'obiettivo &egrave; parzialmente raggiunto;<br>
                                    Se il valore dell'indicatore &egrave; maggiore del 50% l'obiettivo &egrave; raggiunto.
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
                                    <x-chartjs-component :chart="$dataView['mmgCoinvolti']" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Struttura</th>
                                            <th>Totale MMG</th>
                                            <th>MMG Coinvolti</th>
                                            <th>Percentuale (%)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($dataView['mmg'] as $idStruttura => $row)
                                        <tr>
                                            <td>{{ $row['nome_struttura'] }} </td>                                            
                                            <td>{{ $row['mmg_totale'] }} </td>                                            
                                            <td>{{ $row['mmg_coinvolti'] }} </td>                                            
                                            <td class="text-white text-center" style="background-color:{{$row['backgroundMMG']}};">{{ $row['percentuale'] }} </td>                                            
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="legend p-3 border rounded">
                            <strong>Scala valori di riferimento</strong><br>
                            <span>Se il valore dell'indicatore &egrave; maggiore del 60% l'obiettivo &egrave; pienamente raggiunto.<br />
                            Se il valore dell'indicatore &egrave; compreso tra il 20% e il 60% l'obiettivo &egrave; parzialmente raggiunto.<br />
                            Se il valore dell'indicatore &egrave; minore del 20% l'obiettivo &egrave; non raggiunto.<br /></span>
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
                                    <x-chartjs-component :chart="$dataView['prestazioniInappropriateChart']" />
                                </div>
                            </div>

                            <div class="col-md-6">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Struttura</th>
                                            <th>Prest. inappropriate</th>
                                            <th>Prest. totali</th>
                                            <th>Percentuale (%)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($dataView['prestazioniInappropriate'] as $idStruttura => $row)
                                        <tr>
                                            <td>{{ $row['nome_struttura'] }} </td>                                            
                                            <td>{{ $row['numeratore_totale'] }} </td>                                            
                                            <td>{{ $row['denominatore_totale'] }} </td>                                            
                                            <td class="text-white text-center" style="background-color:{{$row['backgroundInappropriate']}};">{{ $row['percentuale'] }} </td>                                            
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <br>
                        <div class="legend p-3 border rounded">
                            <strong>Scala valori di riferimento (Punteggio massimo 1)</strong><br>
                            <span>Se il valore dell'indicatore è compresa tra 0% e 10% l'obiettivo è pienamente raggiunto (1
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
                                                        @if($categoria == 9)
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
                                    <div class="legend p-3 border rounded">
                                        <strong>Scala valori di riferimento</strong><br>
                                        <span>Se il valore dell'indicatore &egrave; superato, l'obiettivo è pienamente raggiunto;<br />
                                        Se il valore dell'indicatore non &egrave; superato, l'obiettivo è non raggiunto.</span><br />
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
                                                        @if($categoria == 10)
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
                                    <div class="legend p-3 border rounded">
                                        <strong>Scala valori di riferimento</strong><br>
                                        <span>Se il valore dell'indicatore &egrave; superato, l'obiettivo è pienamente raggiunto;<br />
                                        Se il valore dell'indicatore non &egrave; superato, l'obiettivo è non raggiunto.</span><br />
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
                                                        @if($categoria == 11)
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
                                    <div class="legend p-3 border rounded">
                                        <strong>Scala valori di riferimento</strong><br>
                                        <span>Se il valore dell'indicatore &egrave; superato, l'obiettivo è pienamente raggiunto;<br />
                                        Se il valore dell'indicatore non &egrave; superato, l'obiettivo è non raggiunto.</span><br />
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