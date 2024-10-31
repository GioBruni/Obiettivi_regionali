@extends('bootstrap-italia::page')

@section("bootstrapitalia_js")
    <script src="{{ asset(path: 'js/chart.js') }}" rel="text/javascript"></script>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <!--<div class="card-header">{{ __('Scrivania') }}</div>-->

                <div class="card-body">
                    @if (session(key: 'status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if (Auth::user()->hasRole('uploader'))
                        <div class="row">
                            <div class="col">
                                <h4 class="mb-4 " id="titleEx6">Il tuo utente &egrave; associato alle seguenti strutture</h4>
                            </div>
                        </div>
                        <div class="row gy-3">
                            @foreach($dataView['userStructures'] as $structure)
                                <div class="col-12 col-md-6 p-2">
                                    <div class="card shadow">
                                        <div class="card-body">
                                            <p class="card-text text-center">
                                                <p class="h2 text-center">{{ $structure->name }}</p>
                                            </p>
                                            <div class="row text-center">
                                                <h1 class="display-1"><i class="bi bi-hospital"></i></h1>
                                            </div>
                                            <p class="card-text font-serif">
                                                Azienda: <strong>{{ $structure->company_code }}</strong><br />
                                                Struttura: <strong>{{ $structure->structure_code }}</strong><br />
                                                Indirizzo: <strong>{{ $structure->address }}</strong>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="row mt-4">
                            <div class="col">
                                <h4 class="mb-4 " id="titleEx6">Punteggi</h4>
                            </div>
                        </div>
                        <div class="row gy-3">
                            @foreach ($dataView['punteggi'] as $struttura => $punteggi)
                                <div><h4>{{ $struttura }}</h4></div>
                                <table class="table">
                                    <thead class="table-light">
                                    <tr>
                                        <th scope="col">Obiettivo</th>
                                        <th scope="col">Punteggio</th>
                                        <th scope="col">Punteggio raggiunto</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($punteggi as $punteggio)
                                            <tr>
                                                <td scope="col">{{ $punteggio['target'] . (isset($punteggio['sub_target']) ? " - " . $punteggio['sub_target'] : "") }}</td>
                                                <td scope="col">{{ $punteggio['points'] }}</td>
                                                <td scope="col">{{ $punteggio['real_points'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endforeach
                        </div>

                        <div class="row">
                            <div class="col">
                                <h4 class="mb-4 " id="titleEx6">Chart</h4>
                            </div>
                        </div>
                        <div class="row">
                            <div style="width:75%;">
                                <x-chartjs-component :chart="$dataView['chart']" />
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection