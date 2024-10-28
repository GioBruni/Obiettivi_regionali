@extends('bootstrap-italia::page')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ isset($dataView['user']) ? 'Utente: ' . $dataView['user']->name : "Nuovo utente"}}</div>

                <div class="card-body">
                    @if (session(key: 'status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    <form action="{{ route(name: "registerUser") }}" method="post">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ isset($dataView['user']) ? $dataView['user']->id  : ""}}">
                        <div class="row">
                            <div class="col-2">Nome *</div>
                            <div class="col-4"><input type="text" name="name" class="form-control" value="{{ isset($dataView['user']) ? $dataView['user']->name : ""}}" required></div>
                        </div>
                        <div class="row">
                            <div class="col-2">Email *</div>
                            <div class="col-4"><input type="email" name="email" class="form-control" value="{{ isset($dataView['user']) ? $dataView['user']->email : "" }}" required></div>
                        </div>
                        <div class="row mt-1">
                            <div class="col-2">Abilitato</div>
                            <div class="col-2">
                                <select name="enable" required>
                                    <option value="{{ config("constants.NO_ACTIVE") }}" {{ isset($dataView['user']) && $dataView['user']->enable == config("constants.NO_ACTIVE") ? "selected" : "" }}>Non attivo</option>
                                    <option value="{{ config("constants.ACTIVE") }}" {{ isset($dataView['user']) && $dataView['user']->enable == config("constants.ACTIVE") ? "selected" : "" }}>Attivo</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-1">
                            <div class="col-2"><button type="submit" class="btn btn-primary"><i class="bi bi-save"></i>&nbsp;&nbsp;Salva</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if(isset($dataView['userStructures']))
            <div class="col-md-12 mt-2">
                <div class="card">
                    <div class="card-header">Strutture collegate</div>

                    <div class="card-body">
                        <form action="{{ route(name: "structure.insert") }}" method="post">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ isset($dataView['user']) ? $dataView['user']->id  : ""}}">
                            <div class="row mt-1">
                                <div class="col-2">Struttura *</div>
                                <div class="col-4">
                                    <select name="structure_id">
                                        @foreach($dataView['structures'] as $structure)
                                            <option value="{{ $structure->id }}">Azienda: {{ $structure->company_code }} | Struttura: {{ $structure->structure_code }} | {{ $structure->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mt-1">
                                <div class="col-2"><button type="submit" class="btn btn-primary"><i class="bi bi-building-add"></i>&nbsp;&nbsp;Aggiugi struttura</button></div>
                            </div>
                        </form>

                        <div class="row mt-4">
                            <div class="col-1 border-bottom"><strong>Regione</strong></div>
                            <div class="col-1 border-bottom"><strong>Azienda</strong></div>
                            <div class="col-1 border-bottom"><strong>Struttura</strong></div>
                            <div class="col-3 border-bottom"><strong>Nome</strong></div>
                            <div class="col"></div>
                        </div>
                        @foreach ($dataView['userStructures'] as $structure)
                            <div class="row p-1">
                                <div class="col-1 border-bottom">{{ $structure->region_code }}</div>
                                <div class="col-1 border-bottom">{{ $structure->company_code }}</div>
                                <div class="col-1 border-bottom">{{ $structure->structure_code }}</div>
                                <div class="col-3 border-bottom">{{ $structure->name }}</div>
                                <div class="col">
                                    <form action="{{ route("structure.delete") }}" method="post">
                                        @csrf
                                        <input type="hidden" name="structure_id" value="{{ $structure->us_id }}" />
                                        <input type="hidden" name="user_id" value="{{ isset($dataView['user']) ? $dataView['user']->id  : ""}}">
                                        <button type="submit" class="btn btn-sm btn-warning"><i class="bi bi-trash3"></i>&nbsp;&nbsp;Cancella</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
