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
    
                <div class="box mt-4">
                    @if (session(key: 'status'))
                        <div class="alert alert-success" role="alert">
                            <h4 class="alert-heading px-5">Dato importato</h4>
                            <p>
                                {{ session('status') }}
                            </p>
                        </div>
                    @endif

                    <div class="card-body">
                        <div class="card-header bg-success text-white mb-3">
                            Ob. 1.1 - Raggiungere la totalità dell'offerta pubblica e privata accreditata negli ambiti territoriali di garanzia tramite i CUP delle aziende sanitarie
                        </div>
                        <div class="col-12 col-md-12">

                            <div class="box mt-4">
                                <div class="card-header bg-primary text-white mb-3">
                                    Modello da scaricare
                                </div>
                                <div class="card-body">
                                    <ul>
                                        <li><a href="/download/ob1_upload_liste_attesa.csv" target="_blank">ob1_upload_liste_attesa.csv</a></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="box mt-4">
                                <div class="card-header bg-primary text-white mb-3">
                                    Caricamento dati
                                </div>
                                <div class="card-body">

                                    @if(isset($dataView['errors']))
                                        <div class="overflow-auto alert alert-danger" style="max-height: 200px;" role="alert">
                                        <h4 class="alert-heading px-5">Nessun dato importato</h4>
                                        <p>
                                        @foreach($dataView['errors'] as $riga => $errori)
                                            @foreach($errori as $errore)
                                                Riga {{ $riga }}: {{$errore}}<br />
                                            @endforeach
                                        @endforeach
                                        </p>
                                        </div>
                                    @endif
                                    @if(isset($dataView['success']))
                                        <div class="alert alert-success" role="alert">
                                            <h4 class="alert-heading px-5">Dati importati!</h4>
                                            <p>{{ $dataView['success'] }}<br />
                                            Cliccare su Scrivania, poi su Prestazioni sanitarie per visualizzare i dati importati.
                                            </p>
                                        </div>
                                    @endif
                                    
                                    <form action="{{ route('importTarget1') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="row">
                                            <div class="col-3">
                                                <input type="file" name="file" accept=".csv" required class="form-control">
                                            </div>                                
                                            <div class="col-3">
                                                <button type="submit" class="btn btn-primary">Importa dati in CSV</button>
                                            </div>                                
                                        </div>
                                    </form>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="box mt-4">
                    <div class="card-body">
                        <div class="card-header bg-success text-white mb-3">
                            Ob. 1.2 - Favorire la presa in carico dei pazienti affetti da patologie cronico-degenerative e oncologiche (D.L. 73/2024)
                        </div>
                        <div class="col-12 col-md-12">
                            @if(isset($dataView['files']) && count(value: $dataView['files']) > 0)
                                <div class="box mt-4">
                                    <div class="card-header bg-primary text-white mb-3">
                                        Modello da scaricare
                                    </div>
                                    <div class="card-body">
                                        <ul>
                                        </ul>
                                    </div>
                                </div>
                            @endif
                            <div class="box mt-4">
                                <div class="card-header bg-primary text-white mb-3">
                                Generazione autocertificazione da firmare elettronicamente
                                </div>
                                <div class="card-body">
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
                                    <div class="box mt-4">
                                        <form method="POST" action="{{ route('saveTempiListeAttesa') }}">
                                            @csrf
                                            <input type="hidden" name="obiettivo" value="{{ $dataView['obiettivo'] }}" />
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label for="categoria">Anno di riferimento</label>
                                                    <div class="form-group">
                                                        <select class="form-control" name="year" id="year">
                                                            @for ($year = date('Y'); $year >= 2023; $year--)
                                                            <option value="{{ $year }}" {{ $year == date('Y') || (isset($dataView['year']) && $year == $dataView['year']) ? "selected" : "" }}>
                                                                {{ $year }}
                                                            </option>
                                                            @endfor
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="categoria">Seleziona la struttura</label>
                                                    <div class="form-group">
                                                        <select class="form-control" name="structure_id" required>
                                                            <option value="">-- Seleziona --</option>
                                                            @foreach ($dataView['strutture'] as $rowStruttura)
                                                            <option value="{{ $rowStruttura->id }}" {{ count($dataView['strutture']) == 1 || (isset($dataView['structure_id']) && $rowStruttura->id == $dataView['structure_id']) ? "selected" : "" }}>
                                                                {{ $rowStruttura->name }}
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="categoria">Numero di agende dedicate ai PTDA aziendali</label>
                                                    <div class="form-group">
                                                        <input type="number" class="form-control" name="numeroAgende" value="{{ isset($dataView['numeroAgende']) ? $dataView['numeroAgende'] : "" }}" required>
                                                        @error('numeroAgende')
                                                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label for="categoria">Num prest. di controllo prescritte dallo specialista ambulatoriale (Anno di riferimento)</label>
                                                    <div class="form-group">
                                                        <input type="number" class="form-control" name="prestazioniSpecialistaRiferimento" value="{{ isset($dataView['prestazioniSpecialistaRiferimento']) ? $dataView['prestazioniSpecialistaRiferimento'] : "" }}" required>
                                                        @error('prestazioniSpecialistaRiferimento')
                                                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="categoria">Num prest. di controllo prescritte dallo specialista ambulatoriale (Anno precedente rispetto a quello di riferimento)</label>
                                                    <div class="form-group">
                                                        <input type="number" class="form-control" name="prestazioniSpecialistaPrecedente" value="{{ isset($dataView['prestazioniSpecialistaPrecedente']) ? $dataView['prestazioniSpecialistaPrecedente'] : "" }}" required>
                                                        @error('prestazioniSpecialistaPrecedente')
                                                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label for="categoria">Num prest. di controllo prescritte da MMG/PLS (Anno di riferimento)</label>
                                                    <div class="form-group">
                                                        <input type="number" class="form-control" name="prestazioniMMGRiferimento" value="{{ isset($dataView['prestazioniMMGRiferimento']) ? $dataView['prestazioniMMGRiferimento'] : "" }}" required>
                                                        @error('prestazioniMMGRiferimento')
                                                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="categoria">Num prest. di controllo prescritte da MMG/PLS (Anno precedente rispetto a quello di riferimento)</label>
                                                    <div class="form-group">
                                                        <input type="number" class="form-control" name="prestazioniMMGPrecedente" value="{{ isset($dataView['prestazioniMMGPrecedente']) ? $dataView['prestazioniMMGPrecedente'] : "" }}" required>
                                                        @error('prestazioniMMGPrecedente')
                                                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-end mt-3">
                                                <button type="submit" class="btn btn-primary btn-sm" id="submitBtn" >
                                                    <i class="bi bi-floppy"></i>&nbsp;&nbsp;{{ __('Salva') }}
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="box mt-4">
                                <div class="card-header bg-primary text-white mb-3">
                                    Caricamento dati
                                </div>
                                <div class="card-body">
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
                                    <form action="{{ route('file.uploadObiettivo') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="obiettivo" value="1">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label for="categoria">Anno di riferimento</label>
                                                <div class="form-group">
                                                    <select class="form-control" name="anno">
                                                        @for ($year = date('Y'); $year >= 2023; $year--)
                                                        <option value="{{ $year }}">
                                                            {{ $year }}
                                                        </option>
                                                        @endfor
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="categoria">Seleziona la struttura</label>
                                                <div class="form-group">
                                                    <select class="form-control" name="structure_id" required>
                                                        <option value="">-- Seleziona --</option>
                                                        @foreach ($dataView['strutture'] as $rowStruttura)
                                                        <option value="{{ $rowStruttura->id }}">
                                                            {{ $rowStruttura->name }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label for="file">Seleziona il file PDF firmato (Max 5MB)</label>
                                                <div class="form-group">
                                                    <input type="file" class="form-control" id="file" name="file" accept=".pdf" required>
                                                    <div id="file-error" class="alert alert-danger mt-2" style="display:none;">Il file supera i 5MB</div>
                                                    @error('file')
                                                        <div class="alert alert-danger mt-2">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <button type="submit" class="btn btn-primary">Carica</button>                            
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
@endsection

@section('bootstrapitalia_js')
<script>

    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $("#year").change(function() {

            window.location.href = "{{ route("uploadTempiListeAttesa" ) }}" + "/" + $(this).val();
        });

    });
</script>
@endsection
