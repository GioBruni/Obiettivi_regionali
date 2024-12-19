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
                    <h4>
                        <i class="{{ $dataView['icona'] }}"></i>
                        {{ $dataView['titolo'] }}
                    </h4>
                    <small>{{ $dataView['tooltip'] }}</small>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="container">
                            <form action="{{ route('admin.fse') }}" method="GET" enctype="multipart/form-data">
                                <input type="hidden" name="annoCorrente" value="{{ date('Y') }}">

                                <div class="row align-items-end">
                                    <div class="col-md-2">
                                        <label for="annoSelezionato">Anno</label>
                                        <select id="annoSelezionato" class="form-control" name="annoSelezionato">
                                            @for ($anno = date('Y'); $anno >= 2023; $anno--)
                                                <option value="{{ $anno }}" {{($anno == request('annoSelezionato', date('Y'))) ? 'selected' : ''}}>{{ $anno }}</option>
                                            @endfor
                                            @php
                                            for ($anno = date('Y'); $anno >= 2023; $anno--) {
                                                $selected = ($anno == request('annoSelezionato', date('Y'))) ? 'selected' : '';
                                            }
                                            @endphp
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary">Cerca</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div id="indicatore1" class="row justify-content-center mt-4">
                            <div class="col-md-12">
                                <div class="card shadow-sm border-0">
                                    <div class="card-header bg-primary text-white">
                                        <b>{{ __('Indicatore 1: Alimentazione FSE da prestazioni ospedaliere') }}</b>
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
                                                                    <th>Struttura</th>
                                                                    <th>LDO indicizzate</th>
                                                                    <th>Dimissioni</th>
                                                                    <th>% LDO Indicizzate</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @if(isset($dataView['strutture']))
                                                                    @foreach($dataView['strutture'] as $idStruttura => $row)
                                                                    <tr>
                                                                        <td>{{$row['nome_struttura']}}</td>
                                                                        <td>{{$row['dimissioniOspedaliere']}}</td>
                                                                        <td>{{$row['ob7']}}</td>
                                                                        <td>{{$row['percentualeDimissioniOspedaliere']}}</td>
                                                                    </tr>
                                                                    @endforeach
                                                                @endif
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <hr id="separatore2" class="my-4">

                                        <div id="radiologia">
                                            <h5 class="text-center text-secondary mb-3">
                                                {{ __('Verbali di pronto soccorso') }}
                                            </h5>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <x-chartjs-component :chart="$dataView['chartProntoSoccorso']" />
                                                </div>
                                                <div
                                                    class="col-md-8 d-flex flex-column align-items-center justify-content-center">
                                                    <div id="data-table4" class="w-100 mb-3">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>Struttura</th>
                                                                    <th>Verbali indicizzati</th>
                                                                    <th>Dimissioni</th>
                                                                    <th>% Verbali Indicizzati</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                            @if(isset($dataView['strutture']))
                                                                @foreach($dataView['strutture'] as $idStruttura => $row)
                                                                    <tr>
                                                                        <td>{{$row['nome_struttura']}}</td>
                                                                        <td>{{$row['dimissioniPS'] }}</td>
                                                                        <td>{{$row['ob7PS']}}</td>
                                                                        <td>{{$row['percentualePS']}}</td>
                                                                    </tr>
                                                                @endforeach
                                                            @endif
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
                                                                    <th>Struttura</th>
                                                                    <th>Referti indicizzati</th>
                                                                    <th>Prestazioni di laboratorio</th>
                                                                    <th>% Referti Indicizzati</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                            @if(isset($dataView['strutture']))
                                                                @foreach($dataView['strutture'] as $idStruttura => $row)
                                                                    <tr>
                                                                        <td>{{$row['nome_struttura']}}</td>
                                                                        <td>{{$row['prestazioniLab']}}</td>
                                                                        <td>{{$row['ia13']}}</td>
                                                                        <td>{{$row['percentualePrestLab']}}</td>
                                                                    </tr>
                                                                @endforeach
                                                            @endif
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
                                                                    <th>Struttura</th>
                                                                    <th>Referti indicizzati</th>
                                                                    <th>Prestazioni di radiologia</th>
                                                                    <th>% Referti Indicizzati</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                            @if(isset($dataView['strutture']))
                                                                @foreach($dataView['strutture'] as $idStruttura => $row)
                                                                    <tr>
                                                                        <td>{{$row['nome_struttura']}}</td>
                                                                        <td>{{$row['prestazioniRadiologia']}}</td>
                                                                        <td>{{$row['ia14']}}</td>
                                                                        <td>{{$row['percentualeRefRadiologia']}}</td>
                                                                    </tr>
                                                                @endforeach
                                                            @endif
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <hr id="separatore2" class="my-4">

                                        <div id="radiologia">
                                            <h5 class="text-center text-secondary mb-3">
                                                {{ __('Referti di specialistica ambulatoriale') }}
                                            </h5>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <x-chartjs-component
                                                        :chart="$dataView['chartSpecialisticaAmbulatoriale']" />
                                                </div>
                                                <div
                                                    class="col-md-8 d-flex flex-column align-items-center justify-content-center">
                                                    <div id="data-table4" class="w-100 mb-3">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>Struttura</th>
                                                                    <th>Referti indicizzati</th>
                                                                    <th>Prestazioni di specialistica ambulatoriale</th>
                                                                    <th>% Referti Indicizzati</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                            @if(isset($dataView['strutture']))

                                                                @foreach($dataView['strutture'] as $idStruttura => $row)
                                                                    <tr>
                                                                        <td>{{$row['nome_struttura']}}</td>
                                                                        <td>{{$row['specialisticaAmbulatoriale']}}</td>
                                                                        <td>{{$row['ia15']}}</td>
                                                                        <td>{{$row['percentualeAmbulatoriale']}}</td>
                                                                    </tr>
                                                                @endforeach
                                                            @endif
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <hr id="separatore2" class="my-4">

                                        <div id="radiologia">
                                            <h5 class="text-center text-secondary mb-3">
                                                {{ __('Certificati vaccinali') }}
                                            </h5>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <x-chartjs-component
                                                        :chart="$dataView['chartCertificatiVaccinali']" />
                                                </div>
                                                <div
                                                    class="col-md-8 d-flex flex-column align-items-center justify-content-center">
                                                    <div id="data-table4" class="w-100 mb-3">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>Struttura</th>
                                                                    <th>Certificati indicizzati</th>
                                                                    <th>Vaccinati</th>
                                                                    <th>% Certificati Indicizzati</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                            @if(isset($dataView['strutture']))

                                                                @foreach($dataView['strutture'] as $idStruttura => $row)
                                                                    <tr>
                                                                        <td>{{$row['nome_struttura']}}</td>

                                                                        <td>{{$row['certificatiIndicizzati']}}</td>
                                                                        <td>{{$row['vaccinati']}}</td>
                                                                        <td>{{$row['percentualeVaccinati']}}</td>
                                                                    </tr>
                                                                @endforeach
                                                            @endif
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <hr id="separatore2" class="my-4">

                                        <div id="radiologia">
                                            <h5 class="text-center text-secondary mb-3">
                                                {{ __('Alimentazione documenti FSE') }}
                                            </h5>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <x-chartjs-component :chart="$dataView['chartDocumentiFSE']" />
                                                </div>
                                                <div
                                                    class="col-md-8 d-flex flex-column align-items-center justify-content-center">
                                                    <div id="data-table4" class="w-100 mb-3">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>Struttura</th>
                                                                    <th>Documenti indicizzati</th>
                                                                    <th>Prestazioni erogate</th>
                                                                    <th>% Documenti Indicizzati</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                            @if(isset($dataView['strutture']))

                                                                @foreach($dataView['strutture'] as $idStruttura => $row)
                                                                    <tr>
                                                                        <td>{{$row['nome_struttura']}}</td>
                                                                        <td>{{$row['documentiIndicizzati']}}</td>
                                                                        <td>{{$row['ia16']}}</td>
                                                                        <td>{{ $row['percentualePrestErogate']}}</td>
                                                                    </tr>
                                                                @endforeach
                                                            @endif
                                                            </tbody>
                                                        </table>
                                                        <div class="legend p-3 border rounded mt-3 ">
                                                            Calcolo punteggio
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="indicatore1" class="row justify-content-center">
                            <div class="col-md-12">
                                <div class="card shadow-sm border-0">
                                    <div class="card-header bg-primary text-white">
                                        <b>{{ __('Indicatore 3: Documenti in CDA2') }}</b>
                                        <br />
                                    </div>
                                    <div class="card-body">


                                        <div id="radiologia">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <x-chartjs-component :chart="$dataView['chartDocumentiCDA2']" />
                                                </div>
                                                <div
                                                    class="col-md-8 d-flex flex-column align-items-center justify-content-center">
                                                    <div id="data-table4" class="w-100 mb-3">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>Struttura</th>
                                                                    <th>Verbali indicizzati</th>
                                                                    <th>Dimissioni</th>
                                                                    <th>% Verbali Indicizzati</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                            @if(isset($dataView['strutture']))

                                                                @foreach($dataView['strutture'] as $idStruttura => $row)
                                                                    <tr>
                                                                        <td>{{$row['nome_struttura']}}</td>
                                                                        <td>{{$row['documentiIndicizzati']}}</td>
                                                                        <td>{{$row['documentiIndicizzatiCDA2'] }}</td>
                                                                        <td>{{$row['percentualeDocumentiCDA2']}}</td>
                                                                    </tr>
                                                                @endforeach
                                                            @endif
                                                            </tbody>
                                                        </table>
                                                        <div class="legend p-3 border rounded mt-3 ">
                                                            Calcolo punteggio
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="indicatore1" class="row justify-content-center">
                            <div class="col-md-12">
                                <div class="card shadow-sm border-0">
                                    <div class="card-header bg-primary text-white">
                                        <b>{{ __('Indicatore 4: Documenti firmati in PaDES') }}</b>
                                        <br />
                                    </div>
                                    <div class="card-body">


                                        <div id="radiologia">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <x-chartjs-component :chart="$dataView['chartDocumentiPades']" />
                                                </div>
                                                <div
                                                    class="col-md-8 d-flex flex-column align-items-center justify-content-center">
                                                    <div id="data-table4" class="w-100 mb-3">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>Struttura</th>
                                                                    <th>Verbali indicizzati</th>
                                                                    <th>Dimissioni</th>
                                                                    <th>% Verbali Indicizzati</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                            @if(isset($dataView['strutture']))

                                                                @foreach($dataView['strutture'] as $idStruttura => $row)
                                                                    <tr>
                                                                        <td>{{$row['nome_struttura']}}</td>
                                                                        <td>{{$row['documentiPades']}}</td>
                                                                        <td>{{$row['documentiIndicizzatiPades']}}</td>
                                                                        <td>{{$row['percentualeDocumentiPades']}}</td>
                                                                    </tr>
                                                                @endforeach
                                                            @endif
                                                            </tbody>
                                                        </table>
                                                        <div class="legend p-3 border rounded mt-3 ">
                                                            Calcolo punteggio
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
</div>
@endsection