@extends('bootstrap-italia::page')

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

                    @if (Auth::user()->hasRole('controller'))
                        <div class="row">
                            <div class="col">
                                <h4 class="mb-4 " id="titleEx6">Risultato obiettivi</h4>
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
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
