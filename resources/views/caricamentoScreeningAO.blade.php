@extends('bootstrap-italia::page')


@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white text-center">
                    <h4>
                        <i class="{{ $dataView['icona'] }}"></i>
                        {{ $dataView['titolo'] }} AO AOU
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
                        Carica PDF
                    </div>
                    <div class="card-body">
                        <form action="{{ route('uploadFileScreeningAo') }}" method="POST" enctype="multipart/form-data"
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

                            <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="numeratore_ao" class="form-label">Numeratore AO:</label>
                                <input id="numeratore_ao" type="number" class="form-control" name="numeratore_ao" required autocomplete="numeratore_ao">
                            </div>
                                <div class="col-md-6">
                                    <label for="numeratore_ao" class="form-label">Denominatore AO</label>
                                    <input id="numeratore_ao" type="number" class="form-control" name="numeratore_ao" required autocomplete="numeratore_ao">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="file">Seleziona il file PDF firmato digitalmente</label>
                                <div class="form-group">
                                    <input type="file" class="form-control" id="file" name="file" accept=".pdf"
                                        required>
                                    <div id="file-error" class="alert alert-primary mt-2" style="display:none;">
                                        Il file supera i 5MB
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary btn-sm" id="submitBtn">
                                    <i class="bi bi-floppy"></i>&nbsp;&nbsp;{{ __('Salva') }}
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