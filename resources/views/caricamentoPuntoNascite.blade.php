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
                @if (session(key: 'status'))
                    <div class="alert alert-success" role="alert">
                        <h4 class="alert-heading px-5">Dato importato</h4>
                        <p>
                            {{ session('status') }}
                        </p>
                    </div>
                @endif

                @if(isset($dataView['files']) && count(value: $dataView['files']) > 0)
                    <div class="box mt-4">
                        <div class="card-header bg-primary text-white mb-3">
                            Modelli eventualmente da scaricare
                        </div>
                        <div class="card-body">
                            <ul>
                                @foreach ($dataView['files'] as $file)
                                    <li><a href="/download/{{ $file }}" target="_blank">{{ $file }}</a></li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
                    
                <div class="box mt-4">
                    <div class="card-header bg-primary text-white mb-3">
                        Carica modello compilato
                    </div>

                    <div class="card-body">
                        <div class="box mt-4">
                            <form action="{{ route('file.uploadObiettivo') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="obiettivo" value="{{ $dataView['obiettivo'] }}" />
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
                                @if (count(value: $dataView['categorie']) > 0)
                                    <div class="row align-top">
                                        <div class="col-md-6">
                                            <label for="categoria">Seleziona la categoria</label>
                                            <div class="form-group">
                                                <select class="form-control" name="categoria" id="categoriaSelect" required>
                                                    <option value="" data-description="&nbsp;">-- Seleziona --</option>
                                                    @foreach ($dataView['categorie'] as $rowCategoria)
                                                    <option value="{{ $rowCategoria->id }}" data-description="{{ $rowCategoria->description }}">
                                                        {{ $rowCategoria->category }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="categoriaDescription">Descrizione</label>
                                            <div id="categoriaDescription" style="margin-top: 0; border: 1px solid #ccc; padding: 5px; border-radius: 5px;">
                                                <span id="descriptionText">&nbsp;</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <label for="file">Seleziona il file PDF della checklist (Max 5MB)</label>
                                <div class="form-group">
                                    <input type="file" class="form-control" id="file" name="file" accept=".pdf" required>
                                    <div id="file-error" class="alert alert-danger mt-2" style="display:none;">Il file supera i 5MB</div>
                                    @error('file')
                                        <div class="alert alert-danger mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-primary">Carica</button>
                            </form>
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

        $("#categoriaSelect").change(function() {
            var selectedOption = $(this).find('option:selected');
            var description = selectedOption.attr('data-description');

            $('#descriptionText').text(description); 
        });

    });
</script>
@endsection
