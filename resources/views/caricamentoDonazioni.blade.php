
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
                                    <div id="file-error" class="alert alert-danger mt-2" style="display:none;">Il file
                                        supera i 5MB
                                    </div>
                                    @error('file')
                                        <div class="alert alert-danger mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary btn-sm" id="submitBtn">
                                    <i class="bi bi-floppy"></i>&nbsp;&nbsp;{{ __('Carica PDF') }}
                                </button>
                            </div>
                        </form>
                    </div>

                    
                    <div class="card-header bg-primary text-white">
                        Carica i dati
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('uploadDatiDonazione') }}">
                            @csrf
                            <input type="hidden" name="obiettivo" value={{$dataView['obiettivo']}}>
                            @if (isset($dataView['errors']))
                                <div class="overflow-auto alert alert-danger" style="max-height: 200px;" role="alert">
                                    <h4 class="alert-heading px-5">Errore</h4>
                                    <p>
                                    @foreach($dataView['errors'] as $errori)
                                        @foreach($errori as $errore)
                                            {{$errore}}<br />
                                        @endforeach
                                    @endforeach
                                    </p>
                                </div>
                            @endif

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <p class="mb-1">Totale accertamenti</p>
                                    <input id="totale_accertamenti" type="number" class="form-control" name="totale_accertamenti" required
                                        autocomplete="totale_accertamenti" autofocus tabindex="1">
                                </div>

                                <div class="form-group col-md-6">
                                    <p class="mb-1">Numero opposti</p>
                                    <input id="numero_opposti" type="number" class="form-control" name="numero_opposti"
                                        required autocomplete="numero_opposti" tabindex="2">
                                </div>

                                <div class="form-group col-md-6">
                                    <p class="mb-1">Totale cornee</p>
                                    <input id="totale_cornee" type="number" class="form-control" name="totale_cornee"
                                        required autocomplete="totale_cornee" tabindex="2">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <p class="mb-1">Seleziona la struttura</p>
                                    <select id="structure_id" name="structure_id" class="form-control" required
                                        tabindex="4">
                                        <option value="" disabled {{ $dataView['tableData']->isEmpty() ? 'selected' : '' }}>Seleziona</option>
                                        @foreach($dataView['strutture'] as $structure)
                                            <option value="{{ $structure->id }}" 
                                                @if($dataView['tableData']->isNotEmpty() && $structure->id == $dataView['tableData']->first()->structure_id)
                                                    selected
                                                @endif
                                                {{ count(value: $dataView['strutture']) == 1 ? "selected" : "" }}>
                                                {{ $structure->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <p class="mb-1">Anno di riferimento</p>
                                    <select id="anno" name="anno" class="form-control" required tabindex="3">
                                        <option>Seleziona</option>
                                        @for ($anno = date('Y'); $anno >= 2023; $anno--)
                                            <option value="{{ $anno }}" {{ $anno == date('Y') ? "selected" : "" }}>
                                                {{ $anno }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-3">
                                <button type="submit" class="btn btn-primary btn-sm" id="submitBtn"
                                    >
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