@extends('bootstrap-italia::page')

@section("bootstrapitalia_js")
    <script src="{{ asset( 'js/chart.js') }}" rel="text/javascript"></script>
    <script src="{{ asset( 'js/chartjs-adapter-date-fns.js') }}" rel="text/javascript"></script>
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
                    <form action="{{ route("indexFarmaci") }}" method="get">
                        <div class="row">
                            <div class="col-md-2">
                                <label for="year">Anno:</label>
                                <select id="year" class="form-control" name="year" id="year">
                                    @foreach ( $dataView['anni'] as $year )
                                        <option value="{{ $year }}" {{ ($year == date('Y') ) ? " selected" : "" }}>{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="structure_id">Seleziona la struttura:</label>
                                <select class="form-control" name="structure_id" required id="structure_id">
                                    <option value="">-- Seleziona --</option>
                                    @foreach ($dataView['strutture'] as $rowStruttura)
                                    <option value="{{ $rowStruttura->id }}" {{ (count($dataView['strutture']) == 1  || (isset($dataView['pct']) && $dataView['pct']['numeratore']->structure_id == $rowStruttura->id )) ? "selected" : "" }}>
                                        {{ $rowStruttura->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @if ($errors->has('structure_id'))
                                    <span class="text-danger">{{ $errors->first('structure_id') }}</span>
                                @endif
                            </div>
                            <div class="col-md-3">
                                <label for="carica">Carica dati dal sistema</label>
                                <button type="submit" class="btn btn-success" id="carica">Carica</button>
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
                                        <div class="col-md-4">
                                            <div class="row">
                                                <div style="width:75%;">
                                                    <x-chartjs-component :chart="$dataView['chart91']" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                        <div class="row">
                                                <div style="width:75%;">
                                                    Gare con delibere: {{ $dataView['gare']['conDelibere']}}<br />
                                                    Gare totali: {{ $dataView['gare']['totali']}}<br />
                                                    Rapporto: {{ $dataView['gare']['rapporto']}}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="legend p-3 border rounded">
                                        <strong>Valori di riferimento</strong><br>
                                        Obiettivo raggiunto al 100% se il risultato è un rapporto pari o superiore a 95%.
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
                                        <div class="col-md-3">
                                            <label for="numeratore">Numeratore certificato: {{ isset($dataView['pct']['numeratore']) ? $dataView['pct']['numeratore']->numerator : "***" }}</label>
                                        </div>
                                    </div>
                                    <div class="row">                                            
                                        <div class="col-md-6">
                                            <label for="denominatore">Denominatore certificato: {{ isset($dataView['pct']['denominatore']) ? $dataView['pct']['denominatore'] :  "***" }}</label>
                                        </div>
                                    </div>

                                    <div class="row">                                            
                                        <div class="col-md-6">
                                            <label for="denominatore">Rapporto in percentuale: {{ $dataView['pct']['rapporto'] }}</label>
                                        </div>
                                    </div>
                                    <div class="legend p-3 border rounded">
                                        <strong>Valori di riferimento</strong><br>
                                        Obiettivo raggiunto al 100% se il risultato è un rapporto pari o superiore a 80%.
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


@section('bootstrapitalia_js')
<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

    });

</script>
@endsection