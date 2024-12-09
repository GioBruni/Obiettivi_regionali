@extends('bootstrap-italia::page')

@section("bootstrapitalia_js")
    <script src="{{ asset("js/chart.js") }}"></script>
@endsection

@section('content')
<div id="boarding" class="row justify-content-center">
    <div class="col-md-12">
        <h1 class="text-center my-4">Obiettivo 4 - Pronto Soccorso</h1>
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white">
                <b class="h4">{{ __('Indicatore TMP') }}</b> <!-- Increased font size -->
                <br />
                <small class="h6">{{ __('aggiungere descrizione') }}</small> <!-- Adjusted font size -->
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 text-center">
                        <div style="width: 100%; max-width: 400px; margin: auto;">
                            <x-chartjs-component :chart="$dataView['chartTmp']" /> <!-- Primo grafico -->
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h5 class="text-center">Tabella</h5>
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Anno</th>
                                    <th>Mese</th>
                                    <th>Tmp (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dataView['flowEmur'] as $item) 
                                    <tr>
                                        <td>{{ $item->anno }}</td>
                                        <td>{{ str_pad($item->mese, 2, '0', STR_PAD_LEFT) }}</td>
                                        <td>{{ $item->tmp }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="legend p-3 border rounded mt-3 {{ $dataView['messaggioTmp']['class'] }}">
                            <strong>{{ $dataView['messaggioTmp']['text'] }}: {{$overallAverageTmp}} %</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="legend p-3 border rounded">
                <strong>Valori di riferimento</strong><br>
                <strong>TMP &ge; 85%:</strong> pieno raggiungimento dell'obiettivo<br>
                <strong>TMP compreso tra 75% e &lt; 85%:</strong> raggiungimento dell'obiettivo
                al 50%<br>
                <strong>TMP &lt; 75%:</strong> obiettivo non raggiunto
            </div>
        </div>
    </div>
</div>


<div id="boarding" class="row justify-content-center">
    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <b class="h4">{{ __('Indicatore Boarding') }}</b> <!-- Increased font size -->
                    <br />
                    <small class="h6">{{ __('aggiungere descrizione') }}</small> <!-- Adjusted font size -->
                </div>

                <div class="card-body">
                    <div class="row">

                        <div class="col-md-6 text-center">
                            <div style="width: 100%; max-width: 400px; margin: auto;">
                                <x-chartjs-component :chart=" $dataView['chartBoarding']" /> <!-- Secondo grafico -->
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h5 class="text-center">Tabella </h5>
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Anno</th>
                                        <th>Mese</th>
                                        <th>Boarding</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dataView['flowEmur'] as $item) 
                                        <tr>
                                            <td>{{ $item->anno }}</td>
                                            <td>{{ str_pad($item->mese, 2, '0', STR_PAD_LEFT) }}</td>
                                            <td>{{ $item->boarding }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div class="legend p-3 border rounded mt-3 {{ $dataView['messaggioBoarding']['class'] }}">
                                <strong>{{ $dataView['messaggioBoarding']['text'] }}: {{$overallAverageBoarding}}</strong>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="legend p-3 border rounded">
                    <strong>Indicatore 2</strong><br>
                    <strong>Boarding &le; 2%:</strong> pieno raggiungimento dell'obiettivo<br>
                    <strong>Boarding compreso tra 2% e 4%:</strong> raggiungimento dell'obiettivo al
                    50%<br>
                    <strong>Boarding &gt; al 4%:</strong> obiettivo non raggiunto
                </div>
            </div>
        </div>
    </div>
</div>
@endsection