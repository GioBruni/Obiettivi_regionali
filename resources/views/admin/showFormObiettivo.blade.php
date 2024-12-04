@extends('bootstrap-italia::page')

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
                @if (session(key: 'status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="box mt-4">
                    <div class="card-header bg-primary text-white mb-3 mt-2">
                        Modelli caricati
                    </div>

                    <div class="card-body">
                        <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Struttura</th>
                                <th>Num requisiti garantiti</th>
                                <th>Tot requisiti previsti</th>
                                <th>Percentuale *</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($dataView["livelli"] as $livello)
                            <tr>
                                <td>{{ $livello['name'] }}</td>
                                <td class="text-white text-center" style="background-color:{{$livello['backgroundLiv']}};">{{ $livello['numerator'] }}</td>
                                <td class="text-white text-center" style="background-color:{{$livello['backgroundLiv']}};">{{ $livello['denominator'] }}</td>
                                <td class="text-white text-center" style="background-color:{{$livello['backgroundLiv']}};">{{ $livello['percentage'] }} %</td>
                            </tr>                    
                        @endforeach
                        </tbody>
                        <caption><div class="it-list-wrapper"><ul class="it-list">
                            <li>Livello I: 100% verde, obiettivo raggiunto;</li>
                            <li>Livello II: 90% verde chiaro, obiettivo raggiunto;</li>
                            <li>Livello III: 75% giallo, obiettivo raggiunto</li>
                            <li>Inferiore a 75% rosso, obiettivo non raggiunto.</li>
                        </ul></div></caption>
                        </table>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection


@section('bootstrapitalia_js')
<script>

    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $("#categoriaSelect").change(function() {
            var selectedOption = $(this).find('option:selected');
            var description = selectedOption.attr('data-description');

            $('#descriptionText').text(description); 
        });

    });
</script>
@endsection
