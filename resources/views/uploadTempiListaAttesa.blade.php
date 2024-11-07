@extends('bootstrap-italia::page')

@section('content')
<h2 class="text-center my-4">Obiettivo 1: Riduzione dei tempi delle liste di attesa delle prestazioni sanitarie</h2>


<div class="card shadow-sm border-0">
    <div class="card-header bg-primary text-white">
        <b
            class="h4">{{ __('Numero di accertamenti di morte con criterio neurologico/numero di decessi aziendali per grave neurolesione') }}</b>
        <br />
        <small class="h6"></small>
    </div>
    <div class="card-body">

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
                    
                    <form action="{{ route('file.uploadTempiListeAttesa') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-3">
                                <input type="file" name="file" accept=".csv" required class="form-control">
                            </div>                                
                            <div class="col-2">
                                <button type="submit" class="btn btn-primary">Importa dati in CSV</button>
                            </div>                                
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
