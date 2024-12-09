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
                    Area della prevenzione
                </div>
                <div class="card-body">
                <form method="POST" action="{{ route('uploadDatiCombinati') }}">
                    @csrf
                    <input type="hidden" name="obiettivo" value="{{ $dataView['obiettivo'] }}">

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
                                <option value="" disabled {{ $dataView['tableData']->isEmpty() ? 'selected' : '' }}>
                                    Scegli una struttura</option>
                                @foreach($dataView['structures'] as $structure)
                                    <option value="{{ $structure->id }}" @if($dataView['tableData']->isNotEmpty() && $structure->id == $dataView['tableData']->first()->structure_id) selected @endif
                                        {{ count($dataView['structures']) == 1 ? 'selected' : '' }}>
                                        {{ $structure->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="mese" class="form-label">Mese</label>
                            <select id="mese" name="mese" class="form-select" required>
                                <option value="" disabled selected>Scegli un mese</option>
                                <option value="1">Gennaio</option>
                                <option value="2">Febbraio</option>
                                <option value="3">Marzo</option>
                                <option value="4">Aprile</option>
                                <option value="5">Maggio</option>
                                <option value="6">Giugno</option>
                                <option value="7">Luglio</option>
                                <option value="8">Agosto</option>
                                <option value="9">Settembre</option>
                                <option value="10">Ottobre</option>
                                <option value="11">Novembre</option>
                                <option value="12">Dicembre</option>
                            </select>
                        </div>
                    </div>


                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="ob10_1_numeratore" class="form-label">Numeratore 1 :</label>
                            <input id="ob10_1_numeratore" type="text" class="form-control" name="ob10_1_numeratore"
                                required autocomplete="ob10_1_numeratore">
                        </div>

                        <div class="col-md-6">
                            <label for="ob10__denominatore" class="form-label">Denominatore 1:</label>
                            <input id="ob10_1_denominatore" type="text" class="form-control"
                                name="ob10_1_denominatore" required autocomplete="ob10_1_denominatore">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="ob10_2_numeratore" class="form-label">Numeratore 2 :</label>
                            <input id="ob10_2_numeratore" type="text" class="form-control" name="ob10_2_numeratore"
                                required autocomplete="ob10_2_numeratore">
                        </div>

                        <div class="col-md-6">
                            <label for="ob10_2_denominatore" class="form-label">Denominatore 2:</label>
                            <input id="ob10_2_denominatore" type="text" class="form-control"
                                name="ob10_2_denominatore" required autocomplete="ob10_2_denominatore">
                        </div>
                    </div>

                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-floppy"></i>&nbsp;&nbsp;{{ __('Salva') }}
                        </button>
                    </div>
                </form>



                    <form method="POST" action="{{ route('uploadDatiCombinati') }}">
                        @csrf
                        <input type="hidden" name="obiettivo" value="{{ $dataView['obiettivo'] }}">

                        <div class="container my-4">
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-header bg-secondary text-white text-center">
                                    <h4><i class="bi bi-table"></i> Inserimento Dati Veterinaria</h4>
                                </div>

                                <div class="card-body">
                                    <!-- Box contenitore -->
                                    <div class="box">
                                        <!-- Sezione: Aziende Bovine -->
                                        <h5 class="text-secondary mb-3">Aziende Bovine</h5>
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <label for="num_aziende_bovine_controllate">Numero Aziende
                                                    Controllate</label>
                                                <input type="number" name="num_aziende_bovine_controllate"
                                                    id="num_aziende_bovine_controllate" class="form-control" min="0"
                                                    required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="num_aziende_bovine_totali">Numero Aziende Totali</label>
                                                <input type="number" name="num_aziende_bovine_totali"
                                                    id="num_aziende_bovine_totali" class="form-control" min="0"
                                                    required>
                                            </div>
                                        </div>

                                        <!-- Sezione: Aziende Ovicaprine -->
                                        <h5 class="text-secondary mb-3">Aziende Ovicaprine</h5>
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <label for="num_aziende_ovicaprine_controllate">Numero Aziende
                                                    Controllate</label>
                                                <input type="number" name="num_aziende_ovicaprine_controllate"
                                                    id="num_aziende_ovicaprine_controllate" class="form-control" min="0"
                                                    required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="num_aziende_ovicaprine_totali">Numero Aziende Totali</label>
                                                <input type="number" name="num_aziende_ovicaprine_totali"
                                                    id="num_aziende_ovicaprine_totali" class="form-control" min="0"
                                                    required>
                                            </div>
                                        </div>
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <label for="num_capi_ovicaprini_controllati">Numero Capi
                                                    Controllati</label>
                                                <input type="number" name="num_capi_ovicaprini_controllati"
                                                    id="num_capi_ovicaprini_controllati" class="form-control" min="0"
                                                    required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="num_capi_ovicaprini_totali">Numero Capi Totali</label>
                                                <input type="number" name="num_capi_ovicaprini_totali"
                                                    id="num_capi_ovicaprini_totali" class="form-control" min="0"
                                                    required>
                                            </div>
                                        </div>

                                        <!-- Sezione: Aziende Suine -->
                                        <h5 class="text-secondary mb-3">Aziende Suine</h5>
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <label for="num_aziende_suine_controllate">Numero Aziende
                                                    Controllate</label>
                                                <input type="number" name="num_aziende_suine_controllate"
                                                    id="num_aziende_suine_controllate" class="form-control" min="0"
                                                    required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="num_aziende_suine_totali">Numero Aziende Totali</label>
                                                <input type="number" name="num_aziende_suine_totali"
                                                    id="num_aziende_suine_totali" class="form-control" min="0" required>
                                            </div>
                                        </div>

                                        <!-- Sezione: Aziende Equine -->
                                        <h5 class="text-secondary mb-3">Aziende Equine</h5>
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <label for="num_aziende_equine_controllate">Numero Aziende
                                                    Controllate</label>
                                                <input type="number" name="num_aziende_equine_controllate"
                                                    id="num_aziende_equine_controllate" class="form-control" min="0"
                                                    required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="num_aziende_equine_totali">Numero Aziende Totali</label>
                                                <input type="number" name="num_aziende_equine_totali"
                                                    id="num_aziende_equine_totali" class="form-control" min="0"
                                                    required>
                                            </div>
                                        </div>

                                        <!-- Sezione: Allevamenti Apistici -->
                                        <h5 class="text-secondary mb-3">Allevamenti Apistici</h5>
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <label for="num_allevamenti_apistici_controllati">Numero Allevamenti
                                                    Controllati</label>
                                                <input type="number" name="num_allevamenti_apistici_controllati"
                                                    id="num_allevamenti_apistici_controllati" class="form-control"
                                                    min="0" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="num_allevamenti_apistici_totali">Numero Allevamenti
                                                    Totali</label>
                                                <input type="number" name="num_allevamenti_apistici_totali"
                                                    id="num_allevamenti_apistici_totali" class="form-control" min="0"
                                                    required>
                                            </div>
                                        </div>

                                        <!-- Sezione: Esecuzione PNAA e Farmacosorveglianza -->
                                        <h5 class="text-secondary mb-3">Controlli PNAA</h5>
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <label for="pnaa7_esecuzione"> Esecuzione del PNAA</label>
                                                <input type="number" name="pnaa7_esecuzione" id="pnaa7_esecuzione"
                                                    class="form-control" min="0" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="pnaa7_esecuzione_totali"> Esecuzione del PNAA Totali</label>
                                                <input type="number" name="pnaa7_esecuzione_totali"
                                                    id="pnaa7_esecuzione_totali" class="form-control" min="0" required>
                                            </div>
                                        </div>

                                        <h5 class="text-secondary mb-3">Controlli Farmacosorveglianza Veterinaria</h5>
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <label for="controlli_farmacosorveglianza_veterinaria"> Controlli
                                                    Farmacosorveglianza Veterinaria</label>
                                                <input type="number" name="controlli_farmacosorveglianza_veterinaria"
                                                    id="controlli_farmacosorveglianza_veterinaria" class="form-control"
                                                    min="0" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="controlli_farmacosorveglianza_veterinaria_totali"> Controlli
                                                    Farmacosorveglianza Veterinaria Totali</label>
                                                <input type="number"
                                                    name="controlli_farmacosorveglianza_veterinaria_totali"
                                                    id="controlli_farmacosorveglianza_veterinaria_totali"
                                                    class="form-control" min="0" required>
                                            </div>
                                        </div>


                                        <!-- Sezione: File e Anno -->
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <label for="anno">Seleziona Anno</label>
                                                <select name="anno" id="anno" class="form-control" required>
                                                <?php
                                                    $currentYear = date('Y');
                                                    for ($year = 2024; $year <= $currentYear; $year++) {
                                                        echo "<option value=\"$year\">$year</option>";
                                                    }
                                                ?>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Sezione: Struttura -->
                                        <div class="row mb-4">
                                            <div class="col-md-12">
                                                <label for="structure_id" class="form-label">Struttura</label>
                                                <select id="structure_id" name="structure_id" class="form-select"
                                                    required>
                                                    <option value="" disabled selected>Scegli una struttura</option>
                                                    @foreach($dataView['structures'] as $structure)
                                                        <option value="{{ $structure->id }}">{{ $structure->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="mese" class="form-label">Mese</label>
                                                <select id="mese" name="mese" class="form-select" required>
                                                    <option value="" disabled selected>Scegli un mese</option>
                                                    <option value="1">Gennaio</option>
                                                    <option value="2">Febbraio</option>
                                                    <option value="3">Marzo</option>
                                                    <option value="4">Aprile</option>
                                                    <option value="5">Maggio</option>
                                                    <option value="6">Giugno</option>
                                                    <option value="7">Luglio</option>
                                                    <option value="8">Agosto</option>
                                                    <option value="9">Settembre</option>
                                                    <option value="10">Ottobre</option>
                                                    <option value="11">Novembre</option>
                                                    <option value="12">Dicembre</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Bottone di salvataggio -->
                                        <div class="text-center">
                                            <button type="submit" class="btn btn-primary it-btn">Salvaaa</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>


                    <div class="container my-4">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-secondary text-white text-center">
                                <h4><i class="bi bi-table"></i> Inserimento Dati Alimenti</h4>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('uploadDatiCombinati') }}">
                                    @csrf
                                    <input type="hidden" name="obiettivo" value="{{ $dataView['obiettivo'] }}">
                                    <!-- Box contenitore -->
                                    <div class="box">
                                    <h5 class="text-secondary mb-3">Copertura del PNR</h5>
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <label for="copertura_pnr_num"> Copertura del PNR NUM</label>
                                                <input type="number" name="copertura_pnr_num" id="copertura_pnr_num" class="form-control" min="0" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="copertura_pnr_den"> Copertura del PNR DEN</label>
                                                <input type="number" name="copertura_pnr_den" id="copertura_pnr_den" class="form-control" min="0" required>
                                            </div>
                                        </div>

                                        <h5 class="text-secondary mb-3">Copertura del Controllo Residui Fitofarmaci</h5>
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <label for="copertura_fitofarmaci_num"> Copertura del Controllo Residui Fitofarmaci NUM</label>
                                                <input type="number" name="copertura_fitofarmaci_num"
                                                    id="copertura_fitofarmaci_num" class="form-control"
                                                    min="0" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="copertura_fitofarmaci_den"> Copertura del Controllo Residui Fitofarmaci DEN</label>
                                                <input type="number"
                                                    name="copertura_fitofarmaci_den"
                                                    id="copertura_fitofarmaci_den"
                                                    class="form-control" min="0" required>
                                            </div>
                                        </div>

                                        <h5 class="text-secondary mb-3">Copertura del Controllo Additivi Alimentari</h5>
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <label for="copertura_additivi_num"> Copertura del Controllo Additivi Alimentari NUM</label>
                                                <input type="number" name="copertura_additivi_num" id="copertura_additivi_num" class="form-control" min="0" required>
                                            </div>

                                            <div class="col-md-6">
                                                <label for="copertura_additivi_den"> Copertura del Controllo Additivi Alimentari DEN</label>
                                                <input type="number" name="copertura_additivi_den" id="copertura_additivi_den"
                                                    class="form-control" min="0" required>
                                            </div>
                                        </div>

                                        <div class="row mb-4 justify-content-center">
                                            <div class="col-md-4 it-form-group">
                                                <label for="structure_id" class="form-label">Struttura</label>
                                                <select id="structure_id" name="structure_id" class="form-select"
                                                    required>
                                                    <option value="" disabled selected>Scegli una struttura</option>
                                                    @foreach($dataView['structures'] as $structure)
                                                        <option value="{{ $structure->id }}">{{ $structure->name }}</option>
                                                    @endforeach
                                                </select>

                                            </div>
                                            <div class="col-md-4 it-form-group">
                                            <label for="anno" class="form-label">Anno</label>
                                                <select id="anno" name="anno" class="form-select" required>
                                                    <option value="" disabled selected>Scegli un anno</option>
                                                    @for ($anno = date('Y'); $anno >= 2023; $anno--)
                                                        <option value="{{ $anno }}">{{ $anno }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="mese" class="form-label">Mese</label>
                                                <select id="mese" name="mese" class="form-select" required>
                                                    <option value="" disabled selected>Scegli un mese</option>
                                                    <option value="1">Gennaio</option>
                                                    <option value="2">Febbraio</option>
                                                    <option value="3">Marzo</option>
                                                    <option value="4">Aprile</option>
                                                    <option value="5">Maggio</option>
                                                    <option value="6">Giugno</option>
                                                    <option value="7">Luglio</option>
                                                    <option value="8">Agosto</option>
                                                    <option value="9">Settembre</option>
                                                    <option value="10">Ottobre</option>
                                                    <option value="11">Novembre</option>
                                                    <option value="12">Dicembre</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Bottone di salvataggio centrato -->
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary it-btn">Salva</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>



                    <a href="{{ $dataView['tableData']->count() === 0 ? '#' : route('downloadPdf', ['obiettivo' => $dataView['obiettivo']]) }}"
                        class="btn btn-primary @if($dataView['tableData']->count() === 0) disabled @endif"
                        @if($dataView['tableData']->count() === 0) tabindex="-1" aria-disabled="true"
                        onclick="event.preventDefault();" @endif>
                        Scarica Certificazione PDF
                    </a>
                </div>

                <!-- Section: Indicatore 3 -->
                <div class="card-header bg-primary text-white mt-4">
                    Area dell'assistenza distrettuale
                </div>
                <div class="card-body">
                <div class="container my-4">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-secondary text-white text-center">
                                <h4><i class="bi bi-table"></i> Inserimento Dati Ospedalizzazione </h4>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('uploadDatiCombinati') }}">
                                    @csrf
                                    <input type="hidden" name="obiettivo" value="{{ $dataView['obiettivo'] }}">
                                    <!-- Box contenitore -->
                                    <div class="box">
                                    <h5 class="text-secondary mb-3">Tasso di ospedalizzazione standardizzato in età adulta (>= 18)</h5>
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <label for="ob10_at_1_den"> numeratore età adulta</label>
                                                <input type="number" name="ob10_at_1_den" id="ob10_at_1_den" class="form-control" min="0" required>
                                            </div>
                                        </div>

                                        <h5 class="text-secondary mb-3">Tasso di ospedalizzazione standardizzato in età adulta (<= 18)</h5>
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <label for="ob10_at_2_num"> numeratore età pediatrica</label>
                                                <input type="number" name="ob10_at_2_den"
                                                    id="ob10_at_2_num" class="form-control"
                                                    min="0" required>
                                            </div>
                                        </div>

                                        <div class="row mb-4 justify-content-center">
                                            <div class="col-md-4 it-form-group">
                                                <label for="structure_id" class="form-label">Struttura</label>
                                                <select id="structure_id" name="structure_id" class="form-select"
                                                    required>
                                                    <option value="" disabled selected>Scegli una struttura</option>
                                                    @foreach($dataView['structures'] as $structure)
                                                        <option value="{{ $structure->id }}">{{ $structure->name }}</option>
                                                    @endforeach
                                                </select>

                                            </div>
                                            <div class="col-md-4 it-form-group">
                                            <label for="anno" class="form-label">Anno</label>
                                                <select id="anno" name="anno" class="form-select" required>
                                                    <option value="" disabled selected>Scegli un anno</option>
                                                    @for ($anno = date('Y'); $anno >= 2023; $anno--)
                                                        <option value="{{ $anno }}">{{ $anno }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="mese" class="form-label">Mese</label>
                                                <select id="mese" name="mese" class="form-select" required>
                                                    <option value="" disabled selected>Scegli un mese</option>
                                                    <option value="1">Gennaio</option>
                                                    <option value="2">Febbraio</option>
                                                    <option value="3">Marzo</option>
                                                    <option value="4">Aprile</option>
                                                    <option value="5">Maggio</option>
                                                    <option value="6">Giugno</option>
                                                    <option value="7">Luglio</option>
                                                    <option value="8">Agosto</option>
                                                    <option value="9">Settembre</option>
                                                    <option value="10">Ottobre</option>
                                                    <option value="11">Novembre</option>
                                                    <option value="12">Dicembre</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Bottone di salvataggio centrato -->
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary it-btn">Salva</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    
                    <div class="container my-4">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-secondary text-white text-center">
                                <h4><i class="bi bi-table"></i> Inserimento Dati in adi (CIA 1, CIA 2, CIA 3)</h4>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('uploadDatiCombinati') }}">
                                    @csrf
                                    <input type="hidden" name="obiettivo" value="{{ $dataView['obiettivo'] }}">
                                    <!-- Box contenitore -->
                                    <div class="box">
                                    <h5 class="text-secondary mb-3">CIA 1</h5>
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <label for="cia_1_num"> CIA 1 NUM</label>
                                                <input type="number" name="cia_1_num" id="cia_1_num" class="form-control" min="0" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="cia_1_den">CIA 1 DEN</label>
                                                <input type="number" name="cia_1_den" id="cia_1_den" class="form-control" min="0" required>
                                            </div>
                                        </div>

                                        <h5 class="text-secondary mb-3">CIA 2</h5>
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <label for="cia_2_num"> CIA 2 NUM</label>
                                                <input type="number" name="cia_2_num"
                                                    id="cia_2_num" class="form-control"
                                                    min="0" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="cia_2_den">CIA 2 DEN</label>
                                                <input type="number"
                                                    name="cia_2_den"
                                                    id="cia_2_den"
                                                    class="form-control" min="0" required>
                                            </div>
                                        </div>

                                        <h5 class="text-secondary mb-3">CIA 3</h5>
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <label for="cia_3_num"> CIA 3 NUM</label>
                                                <input type="number" name="cia_3_num" id="cia_3_num" class="form-control" min="0" required>
                                            </div>

                                            <div class="col-md-6">
                                                <label for="cia_3_den">CIA 3 DEN</label>
                                                <input type="number" name="cia_3_den" id="cia_3_den"
                                                    class="form-control" min="0" required>
                                            </div>
                                        </div>

                                        <div class="row mb-4 justify-content-center">
                                            <div class="col-md-4 it-form-group">
                                                <label for="structure_id" class="form-label">Struttura</label>
                                                <select id="structure_id" name="structure_id" class="form-select"
                                                    required>
                                                    <option value="" disabled selected>Scegli una struttura</option>
                                                    @foreach($dataView['structures'] as $structure)
                                                        <option value="{{ $structure->id }}">{{ $structure->name }}</option>
                                                    @endforeach
                                                </select>

                                            </div>
                                            <div class="col-md-4 it-form-group">
                                            <label for="anno" class="form-label">Anno</label>
                                                <select id="anno" name="anno" class="form-select" required>
                                                    <option value="" disabled selected>Scegli un anno</option>
                                                    @for ($anno = date('Y'); $anno >= 2023; $anno--)
                                                        <option value="{{ $anno }}">{{ $anno }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="mese" class="form-label">Mese</label>
                                                <select id="mese" name="mese" class="form-select" required>
                                                    <option value="" disabled selected>Scegli un mese</option>
                                                    <option value="1">Gennaio</option>
                                                    <option value="2">Febbraio</option>
                                                    <option value="3">Marzo</option>
                                                    <option value="4">Aprile</option>
                                                    <option value="5">Maggio</option>
                                                    <option value="6">Giugno</option>
                                                    <option value="7">Luglio</option>
                                                    <option value="8">Agosto</option>
                                                    <option value="9">Settembre</option>
                                                    <option value="10">Ottobre</option>
                                                    <option value="11">Novembre</option>
                                                    <option value="12">Dicembre</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Bottone di salvataggio centrato -->
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary it-btn">Salva</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="container my-4">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-secondary text-white text-center">
                                <h4><i class="bi bi-table"></i> Numero deceduti per causa di tumore assistiti dalla Rete di cure palliative sul numero deceduti per causa di tumore</h4>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('uploadDatiCombinati') }}">
                                    @csrf
                                    <input type="hidden" name="obiettivo" value="{{ $dataView['obiettivo'] }}">
                                    <!-- Box contenitore -->
                                    <div class="box">
                                    <h5 class="text-secondary mb-3">Dati</h5>
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <label for="cia_1_num">Numeratore</label>
                                                <input type="number" name="ob10_ao_4_num" id="cia_1_num" class="form-control" min="0" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="cia_1_den">Denominatore</label>
                                                <input type="number" name="ob10_ao_4_den" id="cia_1_den" class="form-control" min="0" required>
                                            </div>
                                        </div>

                                        <div class="row mb-4 justify-content-center">
                                            <div class="col-md-4 it-form-group">
                                                <label for="structure_id" class="form-label">Struttura</label>
                                                <select id="structure_id" name="structure_id" class="form-select"
                                                    required>
                                                    <option value="" disabled selected>Scegli una struttura</option>
                                                    @foreach($dataView['structures'] as $structure)
                                                        <option value="{{ $structure->id }}">{{ $structure->name }}</option>
                                                    @endforeach
                                                </select>

                                            </div>
                                            <div class="col-md-4 it-form-group">
                                            <label for="anno" class="form-label">Anno</label>
                                                <select id="anno" name="anno" class="form-select" required>
                                                    <option value="" disabled selected>Scegli un anno</option>
                                                    @for ($anno = date('Y'); $anno >= 2023; $anno--)
                                                        <option value="{{ $anno }}">{{ $anno }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="mese" class="form-label">Mese</label>
                                                <select id="mese" name="mese" class="form-select" required>
                                                    <option value="" disabled selected>Scegli un mese</option>
                                                    <option value="1">Gennaio</option>
                                                    <option value="2">Febbraio</option>
                                                    <option value="3">Marzo</option>
                                                    <option value="4">Aprile</option>
                                                    <option value="5">Maggio</option>
                                                    <option value="6">Giugno</option>
                                                    <option value="7">Luglio</option>
                                                    <option value="8">Agosto</option>
                                                    <option value="9">Settembre</option>
                                                    <option value="10">Ottobre</option>
                                                    <option value="11">Novembre</option>
                                                    <option value="12">Dicembre</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Bottone di salvataggio centrato -->
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary it-btn">Salva</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

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





@endsection