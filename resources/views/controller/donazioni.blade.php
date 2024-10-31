@extends('bootstrap-italia::page')


@section("bootstrapitalia_js")
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3"></script>
@endsection






@section('content')
<h1 class="text-center my-4">Obiettivo 6 - Donazioni</h1>


<div class="card shadow-sm border-0">
    <div class="card-header bg-primary text-white">
        <b class="h4">{{ __('Numero di accertamenti di morte con criterio neurologico/numero di decessi aziendali per grave neurolesione') }}</b> <!-- Increased font size -->
        <br />
        <small class="h6">{{ __('aggiungere descrizione') }}</small> <!-- Adjusted font size -->
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 text-center">
                <div style="width: 100%; max-width: 400px; margin: auto;">
                    <x-chartjs-component :chart=" $dataView['chartDonazioni']" /> <!-- Secondo grafico -->
                </div>
            </div>


            <div class="col-md-6">
                <h5 class="text-center">Tabella</h5>
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Struttura</th>
                            <th>Anno</th>
                            <th>Mese</th>
                            <th>sdo_dato</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>

            </div>
        </div>
    </div>


</div>



@endsection