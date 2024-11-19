@extends('bootstrap-italia::page')

@section('content')

<div class="modal" id="modalEsito" aria-labelledby="modalEsitoTitle">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEsitoTitle">Esito</h5>
            </div>
            <form id="esitoForm" action="{{ route("approvaObiettivo") }}" method="post">
                @csrf
                <input type="hidden" id="fileId" name="fileId" value="">
                <div class="modal-body">
                    <!-- Obiettivo -->
                    <div class="row">
                        <label class="form-label">Obiettivo 8 - <strong><span id="numeroObiettivo"></span></strong></label>
                    </div>

                    <!-- Numeratore e Denominatore (nascosti per ora) -->
                    <!--div class="row mt-3" id="numeratoreDenominatoreFields">
                        <div class="col-md-6">
                            <label for="numeratore" class="form-label">Numeratore: (numero di requisiti raggiunti del file)</label>
                        </div>
                        <div class="col-md-6">
                            <input type="number" id="numeratore" name="numeratore" class="form-control" min="0">
                        </div>
                        <div class="col-md-6">
                            <label for="denominatore" class="form-label">Denominatore: (totale requisiti richiesti dal file)</label>
                        </div>
                        <div class="col-md-6">
                            <input type="number" id="denominatore" name="denominatore" class="form-control" min="0">
                        </div>
                    </div-->

                    <!-- Esito -->
                    <div class="row">
                        <label class="form-label">Esito *</label>
                    </div>
                    <div class="row">
                        <div class="form-check">
                            <input type="radio" class="form-select" id="esitoOk" name="esito" value="1" required>
                            <label for="esitoOk" class="form-label">Favorevole</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" class="form-select" id="esitoNo" name="esito" value="0">
                            <label for="esitoNo" class="form-label">Non favorevole</label>
                        </div>
                    </div>

                    <!-- Note -->
                    <div class="row mt-3">
                        <label class="form-label">Note all'utente:</label>
                    </div>
                    <div class="row">
                        <textarea name="notes" class="form-control" cols="5" style="width:100%;"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Chiudi</button>
                    <button type="submit" class="btn btn-success btn-sm" id="save-modifications">Salva e inoltra</button>
                </div>
            </form>                    
        </div>
    </div>
</div>

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
                                            <button id="esita{{ $file->id }}" data-id="{{ $file->id }}" data-subCategoryId="{{ $file->category }}" class="btn btn-primary"  data-bs-toggle="modal" data-bs-target="#modalEsito">
                                                <i class="bi bi-pencil-square" style="font-size: 1rem;"></i>&nbsp;Esita
                                            </button>
                                        @elseif($file->approved == 1)
                                            Approvato
                                        @else
                                            Non appr.
                                        @endif
                                    </td>
                                    <td>{{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $file->updated_at)->format('d/m/Y H:i') }}</td>
                                </tr>
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

        $('#modalEsito').on('show.bs.modal', function(e) {
            $('#numeratore').val('');
            $('#denominatore').val('');
        });

        $("[id^='nota']").hide();

        $("[id^='esita']").click(function(){
            $('#fileId').val($(this).attr("data-id"));
            $('#numeroObiettivo').text($(this).attr("data-subCategoryId"));
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
