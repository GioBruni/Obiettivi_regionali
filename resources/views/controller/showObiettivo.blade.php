@extends('bootstrap-italia::page')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="row">
                <h3>Obiettivo {{ $dataView['obiettivo'] }} - {{ $dataView['titolo'] }}</h3>
            </div>
            <div class="card mt-2">
                <div class="card-header">Files caricati</div>

                <div class="card-body">
                    <table class="table" id="ob3_table">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Nome file</th>
                                @if (count(value: $dataView['categorie']) > 0)
                                    <th scope="col">Categoria</th>
                                @endif
                                <th scope="col">Data caricamento</th>
                                <th scope="col">Struttura - utente</th>
                                <th scope="col">Appr</th>
                                <th scope="col">Ult aggiornamento</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dataView['filesCaricati'] as $file)

                                <tr class="{{ $file->validator_user_id == null ? "" : ($file->approved == 1 ? "table-success" : "table-danger") }}">
                                    <td scope="row"><a href="{{ $file->path }}" target="_blank">{{ strlen($file->filename) >= 20 ?  substr($file->filename, 0, 20) . "..." : $file->filename }}</a></td>
                                    @if (count($dataView['categorie']) > 0)
                                        <td>{{ substr($file->category, 0, 20) . "..." }}</td>
                                    @endif
                                    <td>{{ Carbon\Carbon::createFromFormat( 'Y-m-d H:i:s', $file->created_at)->format('d/m/Y H:i')  }}</td>
                                    <td>{{ $file->struttura }} - {{ $file->utente }}</td>
                                    <td>
                                        @if($file->validator_user_id == null)
                                            <button name="esita{{ $file->id }}" id="esita{{ $file->id }}" data-id="{{ $file->id }}" class="btn btn-primary">Esita</button>
                                        @elseif($file->approved == 1)
                                            Approvato
                                        @else
                                            Non appr.
                                        @endif
                                    </td>
                                    <td>{{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $file->updated_at)->format('d/m/Y H:i') }}</td>
                                </tr>
                                <form id="form{{ $file->id }}">
                                    <input type="hidden" name="id" value="{{ $file->id }}">
                                    <input type="hidden" name="t" value="{{ $dataView['obiettivo'] }}">
                                    <tr id="nota{{ $file->id }}">
                                        <td colspan="4"><textarea placeholder="Inserisci eventuale nota" name="notes{{$file->id}}">{{ $file->notes }}</textarea></td>
                                        <td colspan="2"><input type="button" class="btn btn-sm btn-success" id="approva{{ $file->id }}" data-id="{{ $file->id }}" value="Approva"><br /><input type="button" class="btn btn-sm btn-danger" id="nonapprova{{ $file->id }}" data-id="{{ $file->id }}" value="Non approva"></td>
                                    </tr>
                                </form>
                            @endforeach
                        </tbody>
                    </table>
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

        $("[id^='nota']").hide();

        $("[id^='esita']").click(function(){
            var id=$(this).attr("data-id");
            $("#nota" + id).toggle();
        });

        $("[id^='approva']").click(function(){
            var id=$(this).attr("data-id");
            var formDaInoltrare = $("#form" + id).serialize();
            
            $.ajax({
                url: '{{ route("controller.valide") }}',
                type: 'POST',
                data: formDaInoltrare,
                success: function(response) {
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    }
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                }
            });
        });

        $("[id^='nonapprova']").click(function(){
            var id=$(this).attr("data-id");
            var formDaInoltrare = $("#form" + id).serialize();
            
            $.ajax({
                url: '{{ route("controller.notValide") }}',
                type: 'POST',
                data: formDaInoltrare,
                success: function(response) {
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    }
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                }
            });
        });

    });
</script>
@endsection
