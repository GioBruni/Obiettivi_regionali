@extends('bootstrap-italia::page')


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

                <br>

                @if (session(key: 'status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        Indicatori di risultato
                    </div>
                    <div class="card-body">
                        <div class="box mt-4">
                            <div class="card-header bg-primary text-white mb-3">
                                Modello CSV da scaricare
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li><a href="/download/ob5_upload_indicatori_LEA.csv" target="_blank">ob5_upload_indicatori_LEA.csv</a></li>                                    
                                </ul>
                            </div>
                        </div>
                        @if (isset($dataView['errorsCSV']))
                            <div class="alert alert-danger mt-1" role="alert">
                                <h4 class="alert-heading px-5">Nessun dato importato</h4>
                                <ul>
                                    @foreach ($dataView['errorsCSV'] as $riga => $errors)
                                        @foreach($errors as $error)
                                            <li>Riga {{ $riga }}: {{ $error }}</li>
                                        @endforeach
                                    @endforeach
                                </ul>
                            </div>
                        @elseif (isset($dataView['successCSV']))
                            <div class="alert alert-success mt-1" role="alert">
                                <h4 class="alert-heading px-5">Dati importati correttamente</h4>
                                <small>{{ $dataView['successCSV'] }}</small>
                            </div>
                        @endif
                        <form action="{{ route('importTarget5LEA') }}" method="POST" enctype="multipart/form-data" >
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="categoria">Seleziona la struttura</label>
                                    <div class="form-group">
                                        <select class="form-control" name="structure_id" required>
                                            <option value="">-- Seleziona --</option>
                                            @foreach ($dataView['strutture'] as $rowStruttura)
                                                <option value="{{ $rowStruttura->id }}" {{ count($dataView['strutture']) == 1 ? "selected" : "" }}>
                                                    {{ $rowStruttura->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="fileCSV">Seleziona il file CSV da caricare</label>
                                <div class="form-group">
                                    <input type="file" class="form-control" id="fileCSV" name="fileCSV" accept=".csv"
                                        required>
                                    <div id="file-error" class="alert alert-danger mt-2" style="display:none;">Il file
                                        supera i 5MB
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary btn-sm" id="submitBtn">
                                    <i class="bi bi-floppy"></i>&nbsp;&nbsp;{{ __('Carica CSV') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <br>

                <div class="card-header bg-primary text-white mt-4">
                    Coinvolgimento e collaborazione MMG per il counseling e la prenotazione diretta dei pazienti in età target non-responder (%MMG aderenti)
                </div>
                <div class="card-body">
                    <div class="card-body">
                        <form method="POST" action="{{ route('mmgRegister') }}">
                            @csrf
                            <input type="hidden" name="obiettivo" value={{$dataView['obiettivo']}}>
                            @if (isset($dataView['errorsMMG']))
                                <div class="overflow-auto alert alert-danger" style="max-height: 200px;" role="alert">
                                    <h4 class="alert-heading px-5">Dati non salvati</h4>
                                    <p>
                                    @foreach($dataView['errorsMMG'] as $key => $errori)
                                        @foreach($errori as $errore)
                                            {{ $errore }}<br />
                                        @endforeach
                                    @endforeach
                                    </p>
                                </div>
                            @endif

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <p class="mb-1">Totale MMG</p>
                                    <input id="tot_mmg" type="number" class="form-control" name="tot_mmg" required
                                        autocomplete="tot_mmg" autofocus tabindex="1"
                                        value="{{ $dataView['tableData']->isNotEmpty() ? $dataView['tableData']->first()->mmg_totale : old('tot_mmg') }}"
                                        @if($dataView['tableData']->isNotEmpty()) disabled @endif>
                                </div>

                                <div class="form-group col-md-6">
                                    <p class="mb-1">MMG Coinvolti</p>
                                    <input id="mmg_coinvolti" type="number" class="form-control" name="mmg_coinvolti"
                                        required autocomplete="mmg_coinvolti" tabindex="2"
                                        value="{{ $dataView['tableData']->isNotEmpty() ? $dataView['tableData']->first()->mmg_coinvolti : '' }}"
                                        @if($dataView['tableData']->isNotEmpty()) disabled @endif>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <p class="mb-1">Anno</p>
                                    <select id="year" name="year" class="form-control" required tabindex="3"
                                        @if($dataView['tableData']->isNotEmpty()) disabled @endif>
                                        <option value="" disabled {{ $dataView['tableData']->isEmpty() ? 'selected' : '' }}>
                                            Scegli
                                            un anno</option>
                                        @for ($year = date('Y'); $year >= 2023; $year--)
                                            <option value="{{ $year }}" {{ $dataView['tableData']->isNotEmpty() && $dataView['tableData']->first()->year == $year ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endfor
                                    </select>

                                </div>


                                <div class="form-group col-md-6">
                                    <p class="mb-1">Struttura</p>
                                    <select id="structure_id" name="structure_id" class="form-control" required
                                        tabindex="4" @if($dataView['tableData']->isNotEmpty()) disabled @endif>
                                        <option value="" disabled {{ $dataView['tableData']->isEmpty() ? 'selected' : '' }}>
                                            Scegli
                                            una struttura</option>
                                        @foreach($dataView['strutture'] as $structure)
                                            <option value="{{ $structure->id }} " {{ count($dataView['strutture']) == 1 ? "selected" : "" }}>
                                                {{ $structure->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>

                            <div class="d-flex justify-content-end mt-3">
                                <button type="submit" class="btn btn-primary btn-sm" id="submitBtn"
                                    @if($dataView['tableData']->isNotEmpty()) disabled @endif>
                                    <i class="bi bi-floppy"></i>&nbsp;&nbsp;{{ __('Salva') }}
                                </button>
                            </div>
                        </form>
                        <a href="{{ $dataView['tableData']->count() === 0 ? '#' : route('downloadPdf', ['obiettivo' => $dataView['obiettivo']]) }}"
                            class="btn btn-primary @if($dataView['tableData']->count() === 0) disabled @endif"
                            @if($dataView['tableData']->count() === 0) tabindex="-1" aria-disabled="true"
                            onclick="event.preventDefault();" @endif>
                            Scarica Certificazione PDF
                        </a>
                    </div>
                </div>
                
                <br>
                
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        Carica PDF
                    </div>
                    <div class="card-body">
                        <form action="{{ route('uploadFileScreening') }}" method="POST" enctype="multipart/form-data"
                            id="caricaPDFForm">
                            @csrf
                            <input type="hidden" name="obiettivo" value={{$dataView['obiettivo']}}>
                            <input type="hidden" name="category" value="category">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="categoria">Seleziona la struttura</label>
                                    <div class="form-group">
                                        <select class="form-control" name="structure_id" required>
                                            <option value="">-- Seleziona --</option>
                                            @foreach ($dataView['strutture'] as $rowStruttura)
                                                <option value="{{ $rowStruttura->id }}" {{ count($dataView['strutture']) == 1 ? "selected" : "" }}>
                                                    {{ $rowStruttura->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="categoria">Anno di riferimento</label>
                                    <div class="form-group">
                                        <select class="form-control" name="anno">
                                            @for ($anno = date('Y'); $anno >= 2023; $anno--)
                                                <option value="{{ $anno }}" {{ $anno == date('Y') ? "selected" : "" }}>
                                                    {{ $anno }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row align-top">
                                <div class="col-md-6">
                                    <label for="categoria">Seleziona la categoria</label>
                                    <div class="form-group">
                                        <select class="form-control" name="categoria" id="categoriaSelect" required>
                                            <option value="" data-description="&nbsp;">-- Seleziona --</option>
                                            @foreach ($dataView['categorie'] as $rowCategoria)
                                                <option value="{{ $rowCategoria->id }}"
                                                    data-description="{{ $rowCategoria->description }}">
                                                    {{ $rowCategoria->category }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="categoriaDescription">Descrizione</label>
                                    <div id="categoriaDescription"
                                        style="margin-top: 0; border: 1px solid #ccc; padding: 5px; border-radius: 5px;">
                                        <span id="descriptionText">&nbsp;</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="file">Seleziona il file PDF firmato digitalmente</label>
                                <div class="form-group">
                                    <input type="file" class="form-control" id="file" name="file" accept=".pdf"
                                        required>
                                    <div id="file-error" class="alert alert-danger mt-2" style="display:none;">
                                        Il file supera i 5MB
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary btn-sm" id="submitBtn">
                                    <i class="bi bi-floppy"></i>&nbsp;&nbsp;{{ __('Carica PDF') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('bootstrapitalia_js')
<script>

    $(document).ready(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $("#categoriaSelect").change(function () {
            var selectedOption = $(this).find('option:selected');
            var description = selectedOption.attr('data-description');

            $('#descriptionText').text(description);
        });

    });

</script>

@endsection