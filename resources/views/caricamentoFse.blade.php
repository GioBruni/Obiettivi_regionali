@extends('bootstrap-italia::page')


@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary text-white text-center">
                    <h4>
                        <i class="{{ $dataView['icona'] }}"></i>
                        {{ $dataView['titolo'] }}
                    </h4>
                    <small>{{ $dataView['tooltip'] }}</small>
                </div>

                @if (session(key: 'status'))
                    <div class="alert alert-success mt-3" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <!-- Section: Indicatore 1 -->
                <div class="card-header bg-primary text-white mt-4">
                    Indicatore 1: Alimentazione FSE da prestazioni ospedaliere
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('uploadDatiFse') }}">
                        @csrf
                        <input type="hidden" name="obiettivo" value="{{ $dataView['obiettivo'] }}">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="prestazioni_ospedaliere" class="form-label">Seleziona:</label>
                                <select id="prestazioni_ospedaliere" name="prestazioni_ospedaliere" class="form-select" required>
                                <option value="">-- Seleziona --</option>
                                    <option value="1">Dimissioni Ospedaliere</option>
                                    <option value="2">Dimissioni Pronto Soccorso</option>
                                    <option value="3">Prestazioni di laboratorio</option>
                                    <option value="4">Prestazioni di radiologia</option>
                                    <option value="5">Prestazioni ambulatoriali</option>
                                    <option value="6">Vaccinati</option>
                                    <option value="7">Documenti indicizzati</option>
                                    <option value="8">Certificati Indicizzati</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="numeratore_fse" class="form-label">Servizio:</label>
                                <input id="numeratore_fse" type="text" class="form-control" name="numeratore_fse" required autocomplete="numeratore_fse">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="anno" class="form-label">Anno</label>
                                <select id="anno" name="anno" class="form-select" required>
                                    <option value="" disabled selected>Scegli un anno</option>
                                    @for ($anno = date('Y'); $anno >= 2023; $anno--)
                                        <option value="{{ $anno }}">{{ $anno }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="structure_id" class="form-label">Struttura</label>
                                <select id="structure_id" name="structure_id" class="form-select" required>
                                    <option value="" disabled {{ $dataView['tableData']->isEmpty() ? 'selected' : '' }}>Scegli una struttura</option>
                                    @foreach($dataView['structures'] as $structure)
                                        <option value="{{ $structure->id }}" 
                                            @if($dataView['tableData']->isNotEmpty() && $structure->id == $dataView['tableData']->first()->structure_id) selected @endif
                                            {{ count($dataView['structures']) == 1 ? 'selected' : '' }}>
                                            {{ $structure->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-floppy"></i>&nbsp;&nbsp;{{ __('Salva') }}
                            </button>
                        </div>
                    </form>

                    <a href="{{ $dataView['tableData']->count() === 0 ? '#' : route('downloadPdf', ['obiettivo' => $dataView['obiettivo']]) }}"
                        class="btn btn-primary @if($dataView['tableData']->count() === 0) disabled @endif"
                        @if($dataView['tableData']->count() === 0) tabindex="-1" aria-disabled="true" onclick="event.preventDefault();" @endif>
                        Scarica Certificazione PDF
                    </a>
                </div>

                <!-- Section: Indicatore 3 -->
                <div class="card-header bg-primary text-white mt-4">
                    Indicatore 3: Documenti in CDA2
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('uploadDatiFse') }}">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="documenti_cda2" class="form-label">Documenti CDA2:</label>
                                <input id="documenti_cda2" type="text" class="form-control" name="documenti_cda2" required autocomplete="documenti_cda2">
                            </div>
                            <div class="col-md-6">
                                <label for="documenti_indicizzatiCDA2" class="form-label">Documenti Indicizzati CDA2:</label>
                                <input id="documenti_indicizzatiCDA2" type="text" class="form-control" name="documenti_indicizzatiCDA2" required autocomplete="documenti_indicizzatiCDA2">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="anno" class="form-label">Anno</label>
                                <select id="anno" name="anno" class="form-select" required>
                                    <option value="" disabled selected>Scegli un anno</option>
                                    @for ($anno = date('Y'); $anno >= 2023; $anno--)
                                        <option value="{{ $anno }}">{{ $anno }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="structure_id" class="form-label">Struttura</label>
                                <select id="structure_id" name="structure_id" class="form-select" required>
                                    <option value="" disabled {{ $dataView['tableData']->isEmpty() ? 'selected' : '' }}>Scegli una struttura</option>
                                    @foreach($dataView['structures'] as $structure)
                                        <option value="{{ $structure->id }}" 
                                            @if($dataView['tableData']->isNotEmpty() && $structure->id == $dataView['tableData']->first()->structure_id) selected @endif
                                            {{ count($dataView['structures']) == 1 ? 'selected' : '' }}>
                                            {{ $structure->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-floppy"></i>&nbsp;&nbsp;{{ __('Salva') }}
                            </button>
                        </div>
                    </form>

                    <a href="{{ $dataView['tableData']->count() === 0 ? '#' : route('downloadPdf', ['obiettivo' => $dataView['obiettivo']]) }}"
                        class="btn btn-primary @if($dataView['tableData']->count() === 0) disabled @endif"
                        @if($dataView['tableData']->count() === 0) tabindex="-1" aria-disabled="true" onclick="event.preventDefault();" @endif>
                        Scarica Certificazione PDF
                    </a>
                </div>

                <!-- Section: Indicatore 4 -->
                <div class="card-header bg-primary text-white mt-4">
                    Indicatore 4: Documenti firmati in PaDES
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('uploadDatiFse') }}">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="documenti_pades" class="form-label">Documenti in Pades:</label>
                                <input id="documenti_pades" type="text" class="form-control" name="documenti_pades" required autocomplete="documenti_pades">
                            </div>
                            <div class="col-md-6">
                                <label for="documenti_indicizzati_pades" class="form-label">Documenti indicizzati Pades:</label>
                                <input id="documenti_indicizzati_pades" type="text" class="form-control" name="documenti_indicizzati_pades" required autocomplete="documenti_indicizzati_pades">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="anno" class="form-label">Anno</label>
                                <select id="anno" name="anno" class="form-select" required>
                                    <option value="" disabled selected>Scegli un anno</option>
                                    @for ($anno = date('Y'); $anno >= 2023; $anno--)
                                        <option value="{{ $anno }}">{{ $anno }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="structure_id" class="form-label">Struttura</label>
                                <select id="structure_id" name="structure_id" class="form-select" required>
                                    <option value="" disabled {{ $dataView['tableData']->isEmpty() ? 'selected' : '' }}>Scegli una struttura</option>
                                    @foreach($dataView['structures'] as $structure)
                                        <option value="{{ $structure->id }}" 
                                            @if($dataView['tableData']->isNotEmpty() && $structure->id == $dataView['tableData']->first()->structure_id) selected @endif
                                            {{ count($dataView['structures']) == 1 ? 'selected' : '' }}>
                                            {{ $structure->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-floppy"></i>&nbsp;&nbsp;{{ __('Salva') }}
                            </button>
                        </div>
                    </form>
                    <a href="{{ $dataView['tableData']->count() === 0 ? '#' : route('downloadPdf', ['obiettivo' => $dataView['obiettivo']]) }}"
                        class="btn btn-primary @if($dataView['tableData']->count() === 0) disabled @endif"
                        @if($dataView['tableData']->count() === 0) tabindex="-1" aria-disabled="true" onclick="event.preventDefault();" @endif>
                        Scarica Certificazione PDF
                    </a>
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