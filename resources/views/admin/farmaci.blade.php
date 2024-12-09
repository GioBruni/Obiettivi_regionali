@extends('bootstrap-italia::page')

@section("bootstrapitalia_js")
    <script src="{{ asset( 'js/chart.js') }}" rel="text/javascript"></script>
@endsection

@section('bootstrapitalia_css')
<style>
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
</style>
@endsection

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white text-center">
                    <h4>
                        <i class="fas fa-pills"></i>
                        {{ __('Obiettivo 9: Approvvigionamento farmaci e gestione I ciclo di terapia') }}
                    </h4>
                </div>
                <div class="card-body">
                    <form action="{{ route("admin.farmaci") }}" method="get">
                        <div class="row">
                            <div class="col-md-2">
                                <label for="year">Anno:</label>
                                <select id="year" class="form-control" name="year" id="year">
                                    @foreach ( $dataView['anni'] as $year )
                                        <option value="{{ $year }}" {{ ($year == $dataView['annoSelezionato'] ) ? " selected" : "" }}>{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>                            
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-success p-3" id="carica">Carica</button>
                            </div>
                        </div>
                    </form>
    
                    <!-- Indicatore 1 -->
                    <div id="GareCUC" class="row justify-content-center mt-4">
                        <div class="col-md-12">
                            <div class="card shadow-sm border-0">
                                <div class="card-header bg-primary text-white">
                                    <b>{{ __('Obiettivo 9.1') }}</b>
                                    <br />
                                    <small>{{ __('Garantire il recepimento delle risultanze delle
                                                procedure aggiudicate dalla Centrale Unica di Committenza
                                                della Regione Siciliana entro 10 giorni dalla data di trasmissione
                                                del decreto di aggiudicazione.') }}</small>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="row">
                                                <div style="width:75%;">
                                                    <x-chartjs-component :chart="$dataView['chart91']" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="row">
                                                <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Struttura</th>
                                                        <th>Gare con delibere</th>
                                                        <th>Gare totali</th>
                                                        <th>Rapporto (%)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($dataView['gare'] as $struttura => $row)
                                                    <tr>
                                                        <td>{{ $struttura }} </td>                                            
                                                        <td class="text-center" >{{ $row['delibere_approvate'] }} </td>
                                                        <td class="text-center" >{{ $row['gare_approvate'] }} </td>
                                                        <td class="text-white text-center" style="background-color:{{$row['rapporto'] > 95 ? "green" : "red"}};">{{ $row['rapporto'] }} </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                                <caption>
                                                    <div class="it-list-wrapper"><ul class="it-list">
                                                        <li>Obiettivo raggiunto al 100% se il risultato &egrave; un rapporto pari o superiore a 95%.</li>
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

                    <!-- Indicatore 2 -->
                    <div id="PCT" class="row justify-content-center">
                        <div class="col-md-12 mt-4">
                            <div class="card shadow-sm border-0">
                                <div class="card-header bg-primary text-white">
                                    <b>{{ __('Obiettivo 9.2') }}</b>
                                    <br />
                                    <small>{{ __('Ottimizzazione della gestione del I ciclo di terapia a pazienti dimessi sia in DH che in ricovero ordinario.') }}</small>
                                </div>
                                <div class="card-body">    
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="row">
                                                <div style="width:75%;">
                                                    <x-chartjs-component :chart="$dataView['chart92']" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">                                    
                                            <div class="row">
                                                <table class="table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>Struttura</th>
                                                            <th>Numeratore</th>
                                                            <th>Denominatore</th>
                                                            <th>Rapporto (%)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($dataView['pct'] as $struttura => $row)
                                                        <tr>
                                                            <td>{{ $struttura }} </td>                                            
                                                            <td class="text-center" >{{ $row['numeratore'] }} </td>
                                                            <td class="text-center" >{{ $row['denominatore'] }} </td>
                                                            <td class="text-white text-center" style="background-color:{{$row['rapporto'] > 80 ? "green" : "red"}};">{{ $row['rapporto'] }} </td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                    <caption>
                                                        <div class="it-list-wrapper"><ul class="it-list">
                                                            <li>Obiettivo raggiunto al 100% se il risultato &egrave; un rapporto pari o superiore a 80%.</li>
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
            </div>
        </div>
    </div>
</div>
@endsection
