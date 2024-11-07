@extends('bootstrap-italia::page')


@section('content')

<div class="card shadow-sm border-0">
    <div class="card-header bg-primary text-white">
        Inserisci i dati
    </div>
    <div class="card-body">
        <div class="card-body">
            <form method="POST" action="{{ route('mmgRegister') }}">
                @csrf
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <p class="mb-1">Totale MMG</p>
                        <input id="tot_mmg" type="text" class="form-control" name="tot_mmg" required
                            autocomplete="tot_mmg" autofocus tabindex="1"
                            value="{{ $dataView['tableData']->isNotEmpty() ? $dataView['tableData']->first()->mmg_totale : '' }}"
                            @if($dataView['tableData']->isNotEmpty()) disabled @endif>
                    </div>

                    <div class="form-group col-md-6">
                        <p class="mb-1">MMG Coinvolti</p>
                        <input id="mmg_coinvolti" type="text" class="form-control" name="mmg_coinvolti" required
                            autocomplete="mmg_coinvolti" tabindex="2"
                            value="{{ $dataView['tableData']->isNotEmpty() ? $dataView['tableData']->first()->mmg_coinvolti : '' }}"
                            @if($dataView['tableData']->isNotEmpty()) disabled @endif>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <p class="mb-1">Anno</p>
                        <select id="anno" name="anno" class="form-control" required tabindex="3"
                            @if($dataView['tableData']->isNotEmpty()) disabled @endif>
                            <option value="" disabled {{ $dataView['tableData']->isEmpty() ? 'selected' : '' }}>
                                Scegli un anno</option>
                            @for ($year = date('Y'); $year >= 2000; $year--)
                                <option value="{{ $year }}" {{ $dataView['tableData']->isNotEmpty() && $dataView['tableData']->first()->anno == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endfor
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


            <a href="{{ $dataView['tableData']->count() === 0 ? '#' : route('downloadPdf') }}"
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
                <input type="hidden" name="obiettivo" value="5">
                <input type="hidden" name="category" value="category">

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="category">Seleziona categoria:</label>
                        <select name="category" id="category" class="form-control" onchange="toggleDescription()">
                            <option value="">-- Seleziona una categoria --</option>
                            <option value="5">Formazione del Personale dedicato allo screening</option>
                            <option value="6">Adeguamento delle dotazioni organiche</option>
                            <option value="7">Organizzazione di programmi di comunicazione rivolti alla popolazione
                                target
                            </option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <div id="descriptionContainer" style="display: none; margin-top: 15px;">
                            <label for="description">Descrizione:</label>
                            <textarea id="description" name="description" rows="4" class="form-control"
                                readonly></textarea>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="file">Seleziona il file PDF della checklist (Max 5MB)</label>
                    <div class="form-group">
                        <input type="file" class="form-control" id="file" name="file" accept=".pdf" required>
                        <div id="file-error" class="alert alert-danger mt-2" style="display:none;">Il file supera i 5MB
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
    </div>
</div>


    <script>
        function toggleDescription() {
            var categoryId = document.getElementById("category").value;
            var descriptionContainer = document.getElementById("descriptionContainer");

            if (categoryId !== "") {
                fetch(`/get-category-description/${categoryId}`)
                    .then(response => response.json())
                    .then(data => {

                        descriptionContainer.style.display = 'block';

                        document.getElementById("description").value = data.description;
                    })
                    .catch(error => {
                        console.error('Errore durante il recupero della descrizione:', error);
                    });
            } else {

                descriptionContainer.style.display = 'none';
            }
        }
    </script>
    @endsection