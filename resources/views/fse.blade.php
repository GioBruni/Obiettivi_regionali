@extends('bootstrap-italia::page')

@section("bootstrapitalia_js")
<script src="{{ asset("js/chart.js") }}"></script>
@endsection

@section('content')



<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white text-center">
                    <!-- Titolo della sotto sezione con icona -->
                    <h4>
                        <i class="fas fa-file-medical"></i> <!-- Icona corrispondente all'obiettivo -->
                        {{ __('Obiettivo 7: Fascicolo Sanitario Elettronico') }} <!-- Testo del titolo -->
                    </h4>
                    <small>{{ __('') }}</small> <!-- Descrizione -->
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="timeFilter">Intervallo di tempo:</label>
                            <select id="timeFilter" name="timeFilter" class="form-control">
                                <option value="1">Ultima settimana</option>
                                <option value="2">Ultimo mese</option>
                                <option value="3">Ultimi 3 mesi</option>
                                <option value="4">Ultimi 6 mesi</option>
                                <option value="5" selected>Ultimo anno</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="data_inizio">Data Inizio:</label>
                            <input type="date" id="data_inizio" class="form-control"
                                value="{{  $dataView['dataInizioDefault'] }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="data_fine">Data Fine:</label>
                            <input type="date" id="data_fine" class="form-control"
                                value="{{ $dataView['dataFineDefault'] }}">
                        </div>
                        <div class="col-md-1 mb-1 d-flex align-items-end">
                            <button id="searchButton" class="btn btn-primary">Cerca</button>
                        </div>

                        <div id="indicatore1" class="row justify-content-center">
                            <div class="col-md-12">
                                <div class="card shadow-sm border-0">
                                    <div class="card-header bg-primary text-white">
                                        <b>{{ __('Indicatore 1') }}</b>
                                        <br />
                                    </div>
                                    <div class="card-body">
                                        <!-- Sezione I: LDO -->
                                        <div id="ldo">
                                            <h5 class="text-center text-secondary mb-3">
                                                {{ __('Lettere di Dimissioni Ospedaliere') }}
                                            </h5>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <x-chartjs-component
                                                        :chart="$dataView['chartDimissioniOspedaliere']" />
                                                </div>
                                                <div
                                                    class="col-md-8 d-flex flex-column align-items-center justify-content-center">
                                                    <div id="data-table1" class="w-100 mb-3">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>LDO indicizzate</th>
                                                                    <th>Dimissioni</th>
                                                                    <th>% LDO Indicizzate</th>
                                                                    <th>% LDO Non Indicizzate</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>

                                                                <tr>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                </tr>

                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <hr id="separatore1" class="my-4">

                                        <div id="laboratorio">
                                            <h5 class="text-center text-secondary mb-3">
                                                {{ __('Referti di laboratorio') }}
                                            </h5>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <x-chartjs-component
                                                        :chart="$dataView['chartRefertiLaboratorio']" />

                                                </div>
                                                <div
                                                    class="col-md-8 d-flex flex-column align-items-center justify-content-center">
                                                    <div id="data-table3" class="w-100 mb-3">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>Verbali indicizzati</th>
                                                                    <th>Dimissioni</th>
                                                                    <th>% Verbali Indicizzati</th>
                                                                    <th>% Verbali Non Indicizzati</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>

                                                                <tr>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                </tr>

                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <hr id="separatore2" class="my-4">




                                        <div id="radiologia">
                                            <h5 class="text-center text-secondary mb-3">
                                                {{ __('Referti di radiologia') }}
                                            </h5>
                                            <div class="row">
                                                <div class="col-md-4">
                                                <x-chartjs-component :chart="$dataView['chartRefertiRadiologia']" />
                                                </div>
                                                <div
                                                    class="col-md-8 d-flex flex-column align-items-center justify-content-center">
                                                    <div id="data-table4" class="w-100 mb-3">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>Verbali indicizzati</th>
                                                                    <th>Dimissioni</th>
                                                                    <th>% Verbali Indicizzati</th>
                                                                    <th>% Verbali Non Indicizzati</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>

                                                                <tr>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        
                                    </div>
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