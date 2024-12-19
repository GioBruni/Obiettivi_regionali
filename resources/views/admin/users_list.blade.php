@extends('bootstrap-italia::page')

@section('bootstrapitalia_css')
    <style>
        .odd-row {
            background-color: #f1f1f1;
        }
    </style>
@endsection


@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Lista degli utenti') }}</div>

                <div class="card-body">
                    @if (session(key: 'status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    @foreach ($dataView['users'] as $key => $user)
                        <div class="row {{ $key % 2 == 0 ? "odd-row" : ""}} {{ $user->enable == config("constants.ACTIVE") ? "" : "neutral-2-bg-a2"}} p-2">
                            <!--div class="col-2"><a href="{{ url("/showUser", ['id' => $user->id]) }}">{{ $user->name }}</a></div-->
                            <div class="col-2">{{ $user->name }}</div>
                            <div class="col-3">{{ $user->email }}</div>
                            <div class="col-4">{{ $user->locations }}</div>
                            <div class="col text-center">
                                <form method="post" action="{{ route("enableUser") }}">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                                    <button type="submit" class="btn btn-success"><i class="bi bi-toggle-{{ $user->enable == config("constants.ACTIVE") ? "off" : "on"}}"></i>&nbsp;&nbsp;{{ $user->enable == config("constants.ACTIVE") ? "Disabilita" : "Abilita"}}</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
