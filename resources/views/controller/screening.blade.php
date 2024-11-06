@extends('bootstrap-italia::page')


@section("bootstrapitalia_js")
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3"></script>
@endsection


@section('content')

<div id="boarding" class="row justify-content-center">
    <div class="col-md-12">
        <h1 class="text-center my-4">Obiettivo 5: Screening</h1>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white">
                <b
                    class="h4">{{ __('Coinvolgimento e collaborazione MMG per il counseling e la prenotazione diretta dei pazienti in età target non-responder (%MMG aderenti)') }}</b>
                <br />
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
        </div>
    </div>
</div>
<br>

@if(count($dataView['tableData']) > 0)
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            Carica PDF
        </div>
        <div class="card-body">
            <form action="{{ route('uploadFileScreening') }}" method="POST" enctype="multipart/form-data" class="d-inline"
                id="caricaPDFForm">
                @csrf
                <input type="hidden" name="obiettivo" value={{ 5 }}>
                <label for="file">Seleziona il file PDF della checklist (Max 5MB)</label>
                <div class="form-group">
                    <input type="file" class="form-control" id="file" name="file" accept=".pdf" required>
                    <div id="file-error" class="alert alert-danger mt-2" style="display:none;">Il file supera i 5MB</div>
                    @error('file')
                        <div class="alert alert-danger mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary btn-sm" id="submitBtn" @if($dataView['file']->count() > 0)
                disabled @endif>
                    <i class="bi bi-floppy"></i>&nbsp;&nbsp;{{ __('Carica PDF') }}
                </button>

            </form>
        </div>
    </div>
@endif

<br>
<div class="card shadow-sm border-0">
    <div class="card-header bg-primary text-white">
        <b
            class="h4">{{ __('Coinvolgimento e collaborazione MMG per il counseling e la prenotazione diretta dei pazienti in età target non-responder (%MMG aderenti)') }}</b>
        <br />
    </div>

    <div class="row justify-content-center mt-4">
        <div class="col-md-6 text-center">
            <div style="width: 100%; max-width: 400px; margin: auto;">
                <x-chartjs-component :chart="$dataView['mmgChart']" />
            </div>
        </div>

        <div class="col-md-6">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Totale MMG</th>
                        <th>MMG Coinvolti</th>
                        <th>Percentuale (%)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dataView['tableData'] as $row)
                        <tr>
                            <td>{{ $row->mmg_totale }}</td>
                            <td>{{ $row->mmg_coinvolti }}</td>
                            <td>{{ number_format($dataView['percentualeCoinvolti'], 2) }}</td>

                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>
    <br>
    <div class="legend p-3 border rounded">
        <strong>Scala valori di riferimento (Punteggio massimo 2)</strong><br>
        <span>Se il valore dell'indicatore &egrave; maggiore del 60% l'obiettivo è pienamente raggiunto (2
            punti).</span><br />
        <span>Se il valore dell'indicatore &egrave; compreso tra il 20% e il 60% l'obiettivo è parzialmente raggiunto (1
            punti).</span><br />
        <span>Se il valore dell'indicatore &egrave; minore del 20% l'obiettivo è non raggiunto (0 punti).</span><br />
    </div>
</div>

<div id="formazione_utenti" class="row justify-content-center mt-3">
    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white">
                <b>{{ __('Formazione del Personale dedicato allo screening') }}</b>
            </div>
            <div class="card-body">
                <div class="row">
                    <!--
                    @if(isset($risultato))
                    @php
                    $categoria_trovata = false;
                    @endphp
                    @foreach($risultato as $categoria_risultato)
                    @if($categoria_risultato->categoria === 'Formazione del personale dedicato allo screening')
                    @php
                    $categoria_trovata = true;
                    @endphp

                    @if($categoria_risultato->validator_user_id === null)
                    <div id="message5" class="message bg-light p-3 rounded border border-primary text-center w-100" style="color: orange;">
                        <strong>File caricato il: {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $categoria_risultato->created_at)->format('d/m/Y H:i') }} in attesa di approvazione.</strong>
                    </div>
                    @else
                    @if($categoria_risultato->approved === 1)
                    <div id="message5" class="message bg-light p-3 rounded border border-primary text-center w-100" style="color:green;">
                        <strong>Il file caricato il: {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $categoria_risultato->created_at)->format('d/m/Y H:i') }} è stato approvato -> Obiettivo raggiunto!</strong>
                    </div>
                    @else
                    <div id="message5" class="message bg-light p-3 rounded border border-primary text-center w-100" style="color:red;">
                        <strong>Il file caricato il: {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $categoria_risultato->created_at)->format('d/m/Y H:i') }} non è stato approvato -> Obiettivo non raggiunto!</strong>
                    </div>
                    @endif
                    @endif
                    @endif
                    @endforeach

                    @if(!$categoria_trovata)
                    <div id="message5" class="message bg-light p-3 rounded border border-primary text-center w-100" style="color:red;">
                        <strong>Il file non è ancora stato caricato -> Obiettivo non raggiunto!</strong>
                    </div>
                    @endif
                    @endif
                    -->
                </div>
                <div class="legend p-3 border rounded">
                    <strong>Scala valori di riferimento (Punteggio massimo 1)</strong><br>
                    <span>Se il valore dell'indicatore &egrave; superato, l'obiettivo è pienamente raggiunto (1
                        punti).</span><br />
                    <span>Se il valore dell'indicatore non &egrave; superato, l'obiettivo è non raggiunto (0
                        punti).</span><br />
                </div>
            </div>
        </div>
    </div>
</div>


<div id="adeguamento_dotazioni_organiche" class="row justify-content-center mt-3">
    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white">
                <b>{{ __('Adeguamento delle dotazioni organiche') }}</b>
            </div>
            <div class="card-body">
                <div class="row">
                    <!--
                    @if(isset($risultato))
                    @php
                    $categoria_trovata = false;
                    @endphp

                    @foreach($risultato as $categoria_risultato)
                    @if($categoria_risultato->categoria === 'Adeguamento delle dotazioni organiche')
                    @php
                    $categoria_trovata = true;
                    @endphp

                    @if($categoria_risultato->validator_user_id === null)
                    <div id="message5" class="message bg-light p-3 rounded border border-primary text-center w-100" style="color: orange;">
                        <strong>File caricato il: {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $categoria_risultato->created_at)->format('d/m/Y H:i') }} in attesa di approvazione.</strong>
                    </div>
                    @else
                    @if($categoria_risultato->approved === 1)
                    <div id="message5" class="message bg-light p-3 rounded border border-primary text-center w-100" style="color:green;">
                        <strong>Il file caricato il: {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $categoria_risultato->created_at)->format('d/m/Y H:i') }} è stato approvato -> Obiettivo raggiunto!</strong>
                    </div>
                    @else
                    <div id="message5" class="message bg-light p-3 rounded border border-primary text-center w-100" style="color:red;">
                        <strong>Il file caricato il: {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $categoria_risultato->created_at)->format('d/m/Y H:i') }} non è stato approvato -> Obiettivo non raggiunto!</strong>
                    </div>
                    @endif
                    @endif
                    @endif
                    @endforeach

                    @if(!$categoria_trovata)
                    <div id="message5" class="message bg-light p-3 rounded border border-primary text-center w-100" style="color:red;">
                        <strong>Il file non è ancora stato caricato -> Obiettivo non raggiunto!</strong>
                    </div>
                    @endif
                    @endif
                    -->
                </div>
                <div class="legend p-3 border rounded">
                    <strong>Scala valori di riferimento (Punteggio massimo 1)</strong><br>
                    <span>Se il valore dell'indicatore &egrave; superato, l'obiettivo è pienamente raggiunto (1 punti).</span><br />
                    <span>Se il valore dell'indicatore non &egrave; superato, l'obiettivo è non raggiunto (0 punti).</span><br />
                </div>
            </div>
        </div>
    </div>
</div>

<div id="programmi_comunicazione" class="row justify-content-center mt-3">
    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white">
                <b>{{ __('Organizzazione di programmi di comunicazione rivolti alla popolazione target') }}</b>
            </div>
            <div class="card-body">
                <div class="row">
                     <!--
                    @if(isset($risultato))
                    @php
                    $categoria_trovata = false; // Variabile per tracciare se la categoria è stata trovata
                    @endphp

                    @foreach($risultato as $categoria_risultato)
                    @if($categoria_risultato->categoria === 'Organizzazione di programmi di comunicazione rivolti alla popolazione target')
                    @php
                    $categoria_trovata = true; // Categoria trovata
                    @endphp

                    @if($categoria_risultato->validator_user_id === null)
                    <div id="message5" class="message bg-light p-3 rounded border border-primary text-center w-100" style="color: orange;">
                        <strong>File caricato il: {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $categoria_risultato->created_at)->format('d/m/Y H:i') }} in attesa di approvazione.</strong>
                    </div>
                    @else
                    @if($categoria_risultato->approved === 1)
                    <div id="message5" class="message bg-light p-3 rounded border border-primary text-center w-100" style="color:green;">
                        <strong>Il file caricato il: {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $categoria_risultato->created_at)->format('d/m/Y H:i') }} è stato approvato -> Obiettivo raggiunto!</strong>
                    </div>
                    @else
                    <div id="message5" class="message bg-light p-3 rounded border border-primary text-center w-100" style="color:red;">
                        <strong>Il file caricato il: {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $categoria_risultato->created_at)->format('d/m/Y H:i') }} non è stato approvato -> Obiettivo non raggiunto!</strong>
                    </div>
                    @endif
                    @endif
                    @endif
                    @endforeach

                    @if(!$categoria_trovata)
                    <div id="message5" class="message bg-light p-3 rounded border border-primary text-center w-100" style="color:red;">
                        <strong>Il file non è ancora stato caricato -> Obiettivo non raggiunto!</strong>
                    </div>
                    @endif
                    @endif
                    -->
                </div>
                <div class="legend p-3 border rounded">
                    <strong>Scala valori di riferimento (Punteggio massimo 1)</strong><br>
                    <span>Se il valore dell'indicatore &egrave; superato, l'obiettivo è pienamente raggiunto (1 punti).</span><br />
                    <span>Se il valore dell'indicatore non &egrave; superato, l'obiettivo è non raggiunto (0 punti).</span><br />
                </div>
            </div>
        </div>
    </div>
</div>

@endsection