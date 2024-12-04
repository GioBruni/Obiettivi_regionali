@extends('bootstrap-italia::page')

@section("bootstrapitalia_js")
    <script src="{{ asset("js/chart.js") }}"></script>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white text-center">
                <h4>
                    <i class="{{ $dataView['icona'] }}"></i>
                    {{ $dataView['titolo'] }}
                </h4>
                <small>{{ $dataView['tooltip'] }}</small>
            </div>
            <div class="card-body">

                <form action="{{ route("admin.prontoSoccorso") }}" method="GET">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="year">Anno:</label>
                            <select name="year" class="form-control">
                                @for ($i = date('Y'); $i >= 2023; $i-- )
                                    <option value="{{ $i }}" {{ (isset($dataView['anno']) && $dataView['anno'] == $i) ? "selected" : "" }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-1 mb-1 d-flex align-items-end p-3">
                            <button id="searchButton" class="btn btn-primary" type="submit">Cerca</button>
                        </div>
                    </div>
                </form>
                <hr class="hr" />

                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-striped table-bordered">
                            <thead>
                            <tr>
                                <th>Struttura</th>
                                <th>Ind. 1: TMP (%)</th>
                                <th>Ind. 2: Boarding (%)</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach($dataView['flowEmur'] as $item) 
                                <tr>
                                    <td>{{ $item['name'] }}</td>
                                    <td class="text-white text-center" style="background-color:{{$item['tmpColor']}};">{{ $item['tmp'] }}</td>
                                    <td class="text-white text-center" style="background-color:{{$item['boardingColor']}};">{{ $item['boarding'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <div class="legend p-3 border rounded">
                            <strong>Valori di riferimento (Indicatore 1)</strong><br>
                            <strong>TMP &ge; 85%:</strong> pieno raggiungimento dell'obiettivo<br>
                            <strong>TMP compreso tra 75% e &lt; 85%:</strong> raggiungimento dell'obiettivo al 50%<br>
                            <strong>TMP &lt; 75%:</strong> obiettivo non raggiunto
                        </div>
                        <div class="legend p-3 border rounded">
                            <strong>Valori di riferimento (Indicatore 2)</strong><br>
                            <strong>Boarding &le; 2%:</strong> pieno raggiungimento dell'obiettivo<br>
                            <strong>Boarding compreso tra 2% e 4%:</strong> raggiungimento dell'obiettivo al 50%<br>
                            <strong>Boarding &gt; al 4%:</strong> obiettivo non raggiunto
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white text-center">
        Rappresentazione TMP per struttura
        </div>
        <div class="card-body">
            <div class="row">
                <div style="width: 100%; margin: auto;">
                    <x-chartjs-component :chart="$dataView['lineChartTmp']" />
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white text-center">
            Rappresentazione Boarding per struttura
        </div>
        <div class="card-body">
            <div class="row">
                <div style="width: 100%; margin: auto;">
                    <x-chartjs-component :chart="$dataView['lineChartBoarding']" />
                </div>
            </div>
        </div>
    </div>
</div>
@endsection