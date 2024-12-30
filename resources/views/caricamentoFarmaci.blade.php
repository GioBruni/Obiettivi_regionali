@extends('bootstrap-italia::page')

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
                        <i class="{{ $dataView['icona'] }}"></i>
                        {{ $dataView['titolo'] }}
                    </h4>
                    <small>{{ $dataView['tooltip'] }}</small>
                </div>
                @if (session(key: 'success'))
                    <div class="alert alert-success" role="alert">
                        <h4 class="alert-heading px-5">Dato importato</h4>
                        <p>
                            {{ session('success') }}
                        </p>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger mt-1" role="alert">
                        <h4 class="alert-heading px-5">Dato non importato</h4>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="card-body">
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
                                    <form action="{{ route("farmaci.gare.autocertificazione") }}" method="post" enctype="multipart/form-data">                                        
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label for="structure_id">Seleziona la struttura:</label>
                                                <select class="form-control" name="structure_id" required id="structure_id">
                                                    <option value="">-- Seleziona --</option>
                                                    @foreach ($dataView['strutture'] as $rowStruttura)
                                                    <option value="{{ $rowStruttura->id }}" {{ (count($dataView['strutture']) == 1  || (isset($dataView['PCT']) && $dataView['PCT']->structure_id == $rowStruttura->id )) ? "selected" : "" }}>
                                                        {{ $rowStruttura->name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                                @if ($errors->has('structure_id'))
                                                    <span class="text-danger">{{ $errors->first('structure_id') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-12">
                                                <div class="legend p-3 border rounded bg-primary text-white "><strong>Gara</strong></div>

                                                <div class="row mt-2">
                                                    <label for="file">File:</label>
                                                    <input type="file" class="form-control" id="file" name="file" accept=".pdf" required>
                                                </div>
                                                <div class="row mt-2">
                                                    <label for="dataAppalto">Data appalto:</label>
                                                    <input type="date" name="dataAppalto" class="form-control" value="" required/>
                                                </div>
                                                <div class="row mt-2">
                                                    <label for="numeroDecreto">Numero decreto:</label>
                                                    <input type="text" name="numeroDecreto" class="form-control" value="" required/>
                                                </div>
                                                <div class="row mt-2">
                                                    <label for="protocolloDecreto">Protocollo decreto:</label>
                                                    <input type="text" name="protocolloDecreto" class="form-control" value="" required/>
                                                </div>
                                                <div class="row mt-2">
                                                    <label for="dataProtocolloDecreto">Data protocollo decreto:</label>
                                                    <input type="date" name="dataProtocolloDecreto" class="form-control" value="" required/>
                                                </div>
                                                <div class="row col-md-6 mt-2">
                                                    <button id="caricaGara" type="submit" class="btn btn-primary" >Carica gara</button>
                                                </div>                                        
                                            </div>
                                        </div>
                                    </form>
                                    <div class="p-2 w-100">
                                        <div class="divider-2"></div>
                                    </div>
                                    <form action="{{ route("farmaci.gare.deliberazione") }}" method="post" enctype="multipart/form-data">                                        
                                        @csrf                                    
                                        <div class="col-md-12">
                                        <div class="legend p-3 border rounded bg-primary text-white "><strong>Delibera</strong></div>

                                            <div class="row mt-2">
                                                <label for="file">Gara caricata:</label>
                                                    <select name="gara" required class="form-control">
                                                        <option value="">Seleziona una gara...</option>
                                                        @foreach($dataView['gare'] as $gara)
                                                            <option value="{{ $gara->id }}">Decr. {{ $gara->numero_decreto }}, prot. {{ $gara->protocollo_decreto }}, dt prot. {{ date("d/m/Y H:s", strtotime($gara->data_protocollo_decreto)) }} (caricata in data {{ date("d/m/Y H:s", strtotime($gara->created_at)) }})</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="row">
                                                    <label for="fileDelibera">File:</label>
                                                    <input type="file" class="form-control" id="fileDelibera" name="fileDelibera" accept=".pdf" required>
                                                </div>
                                                <div class="row mt-2">
                                                    <label for="dataDelibera">Data delibera:</label>
                                                    <input type="date" name="dataDelibera" class="form-control" value="" required/>
                                                </div>
                                                <div class="row mt-2">
                                                    <label for="numeroDelibera">Numero delibera:</label>
                                                    <input type="text" name="numeroDelibera" class="form-control" value="" required/>
                                                </div>
                                                <div class="row col-md-6 mt-2">
                                                    <button id="caricaDelibera" type="submit" class="btn btn-primary" >Carica delibera</button>
                                                </div>                                        
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route("farmaci.pct.autocertificazione") }}" method="post" id="autocertificazioneForm">                                        
                        @csrf

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
                                            <div class="col-md-2">
                                                <label for="numeratore">Seleziona l'anno:</label>
                                                <select name="year" class="form-control">
                                                    @foreach($dataView['anni'] as $anni)
                                                        <option value="{{ $anni }}">{{ $anni }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="structure_id">Seleziona la struttura:</label>
                                                <select class="form-control" name="structure_id" required id="structure_id">
                                                    <option value="">-- Seleziona --</option>
                                                    @foreach ($dataView['strutture'] as $rowStruttura)
                                                    <option value="{{ $rowStruttura->id }}" {{ (count($dataView['strutture']) == 1  || (isset($dataView['PCT']) && $dataView['PCT']->structure_id == $rowStruttura->id )) ? "selected" : "" }}>
                                                        {{ $rowStruttura->name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                                @if ($errors->has('structure_id'))
                                                    <span class="text-danger">{{ $errors->first('structure_id') }}</span>
                                                @endif
                                            </div>
                                            <div class="col-md-3">
                                                <label for="numeratore">Inserire il numeratore</label>
                                                <input type="number" name="numeratore" class="form-control" value=""/>
                                                @if ($errors->has('numeratore'))
                                                    <span class="text-danger">{{ $errors->first('numeratore') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="row mt-3">                                            
                                            <div class="col-md-6">
                                                <label for="denominatore">Dati presenti nel sistema (dai flussi SDO e da input manuale)</label>
                                                <ul>
                                                @foreach ($dataView['pct.denominatori'] as $denominatore)
                                                    @php
                                                        $numeratore = "***";
                                                    @endphp
                                                    @foreach($dataView['PCT'] as $pct)
                                                        @if ($pct['year'] == $denominatore->year)
                                                            @php
                                                                $numeratore = $pct->numerator;
                                                            @endphp
                                                        @endif
                                                    @endforeach
                                                    <li>anno {{ $denominatore->year }}: denominatore {{$denominatore->tot}}, numeratore {{ $numeratore }}</li>
                                                @endforeach
                                                </ul>
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

                        <div class="row mt-3" >
                            <div class="col-md-6">
                                <label for="file">Scarica autocertificazione da firmare digitalmente</label>
                                <button id="generaCertificazione" type="submit" class="btn btn-primary" {{ isset($dataView['PCT']->uploated_file_id) ? "disabled" : ""}}>Genera autocertificazione</button>
                            </div>
                        </div>

                    </form>

                    <div class="col-md-12 mt-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-primary text-white">
                                <b>{{ __('Carica l\'autocertificazione firmata digitalmente') }}</b>
                            </div>
                            <div class="card-body">

                                <div class="row mt-2">
                                    <div class="col-12 col-md-12">

                                        <label for="file">Eseguire la firma elettronica sull'autocertificazione e caricarla nel modulo seguente. Un corretto caricamento assicura che i campi inseriti non siano pi&ugrave; modificabili</label>

                                        <form action="{{ route('farmaci.pct.upload') }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <div class="row">
                                                <div class="col-md-2">
                                                    <label for="year">Anno:</label>
                                                    <select id="year" class="form-control" name="year">
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
                                                        <option value="{{ $rowStruttura->id }}" {{ (count($dataView['strutture']) == 1  || (isset($dataView['PCT']) && $dataView['PCT']->structure_id == $rowStruttura->id )) ? "selected" : "" }}>
                                                            {{ $rowStruttura->name }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                    @if ($errors->has('structure_id'))
                                                        <span class="text-danger">{{ $errors->first('structure_id') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <div class="row mt-4">
                                                <div class="col-md-6">
                                                    <input type="file" class="form-control" id="file" name="file" accept=".pdf" required>
                                                </div>
                                            </div>
                                            <div class="row mt-4">
                                                <div class="col-md-6">
                                                    <button type="submit" class="btn btn-primary" {{ isset($dataView['PCT']->uploated_file_id) ? "disabled" : ""}}>Carica autocertificazione</button>
                                                </div>
                                            </div>
                                        </form>
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