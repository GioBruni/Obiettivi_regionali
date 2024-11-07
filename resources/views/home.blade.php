@extends('bootstrap-italia::page')

@section("bootstrapitalia_js")
    <script src="{{ asset(path: 'js/chart.js') }}" rel="text/javascript"></script>
@endsection

@section('bootstrapitalia_css')
<style>
    .btn-obiettivo {
        display: inline-block;
        width: 120px;
        text-align: center;
        padding: 30px 20px;
        font-size: 30px;
        height: 120px;
        border-radius: 12px !important;
    }

    .btn-obiettivo i {
        display: block;
        font-size: 2.5rem;
    }

    .text-obiettivo {
        margin-top: 5px;
        margin-bottom: 20px;
    }

    .display-4 {
        font-size: 35px !important;
    }

    .box {
        border: 1px solid #ddd;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        background-color: #fff;
        margin-bottom: 15px;
    }

    .tooltip-inner {
        font-size: 0.8rem;
    }
</style>
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

                        <div class="row justify-content-center">
                            <div class="col-12 col-md-12">
                                <div class="box">
                                    <div class="row justify-content-center">
                                        <div class="col-12 text-center mb-5">
                                            <h1 class="display-4">Obiettivi di Salute e Funzionamento</h1>
                                        </div>
                                    </div>
                                    @foreach (array_chunk($dataView['saluteEFunzionamento'], 5) as $chunk)
                                    <div class="row justify-content-center">
                                        @foreach ($chunk as $column)
                                        <div class="col-12 col-md-2 text-center">
                                            <button class="btn btn-primary btn-obiettivo {{$column['enable'] == false ? "disabled" : "" }}" @if ($column['route'])
                                                onclick="window.location.href='{{ $column['route'] }}'" @endif data-bs-toggle="tooltip"
                                                data-bs-placement="top" title="{{ $column['tooltip'] }}">
                                                <i class="{{ $column['icon'] }}"></i>
                                            </button>
                                            <div class="text-obiettivo">{{ $column['text'] }}</div>
                                        </div>
                                        @endforeach
                                    </div>
                                    @endforeach
                                </div>
                            </div>
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
                                        <th scope="col">&nbsp;</th>
                                        <th scope="col">Obiettivo</th>
                                        <th scope="col">Punteggio</th>
                                        <th scope="col">Punteggio raggiunto</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($punteggi as $punteggio)
                                            <tr class="{{ $punteggio['real_points'] > 0 ? "table-info" : ""}}">
                                                <td class="text-end">{{ $punteggio['target_number'] }} </td>
                                                <td >{{ $punteggio['target'] . (isset($punteggio['sub_target']) ? " - " . $punteggio['sub_target'] : "") }}</td>
                                                <td >{{ $punteggio['points'] }}</td>
                                                <td >{{ $punteggio['real_points'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endforeach
                        </div>
<!--
                        <div class="row">
                            <div class="col">
                                <h4 class="mb-4 " id="titleEx6">Chart</h4>
                            </div>
                        </div>
                        <div class="row">
                            <div style="width:75%;">
                                <x -ch artjs-component :cha rt="$ dataVie w['chart']" />
                            </div>
                        </div>
-->                        
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
