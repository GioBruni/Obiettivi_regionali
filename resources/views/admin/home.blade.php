@extends('bootstrap-italia::page')

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
                <div class="card-body">
                    @if (session(key: 'status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if (Auth::user()->hasRole('user_manager'))
                        <div class="row justify-content-center">
                            <div class="col-12 col-md-12">
                                <div class="box">
                                    <div class="row justify-content-center">
                                        <div class="col-12 text-center mb-5">
                                            <h1 class="display-4">Obiettivi di Salute e Funzionamento</h1>
                                        </div>
                                    </div>
                                    @foreach (array_chunk($dataView['saluteEFunzionamento'], 5) as $idP => $chunk)
                                    <div class="row justify-content-center">
                                        @foreach ($chunk as $id => $column)
                                        <div class="col-12 col-md-2 text-center">
                                            <button class="btn btn-primary btn-obiettivo {{in_array($idP*5+$id+1, [10]) ? "disabled" : "" }}" @if ($column['route'])
                                                onclick="window.location.href='{{ $column['routeAdmin'] }}'" @endif data-bs-toggle="tooltip"
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
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
