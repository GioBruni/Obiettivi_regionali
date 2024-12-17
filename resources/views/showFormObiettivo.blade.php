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
                    @foreach ($dataView["categorie"] as $categoria)
                        <?php $trovata = -1; ?>
                        <div class="card shadow-sm border-0 mt-2">
                            <div class="card-header bg-primary text-white">
                                <strong>{{ $categoria->category }}</strong>
                            </div>
                            <div class="card-body">

                                @foreach($dataView['filesCaricati'] as $file)
                                    @if ($file->target_category_id == $categoria->id)
                                        <?php $trovata = 1; ?>
                                        @if($file->validator_user_id === null)
                                            <div id="message5" class="message bg-light p-3 rounded border border-primary text-center w-100" style = "color: orange;">
                                                <strong>File caricato il: {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $file->created_at)->format('d/m/Y H:i') }} in attesa di approvazione.</strong>
                                            </div>
                                        @else
                                            <div id="message5" class="message bg-light p-3 rounded border border-primary text-center w-100" style="color:{{ $file->approved === 1 ? "green" : "red"}};">
                                            <strong>Il file caricato il: {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $file->created_at)->format('d/m/Y H:i') }} {{ $file->approved === 1 ? "" : "non " }}&egrave; stato approvato -> Obiettivo {{ $file->approved === 1 ? "" : "non " }}raggiunto!</strong>
                                            </div>
                                        @endif

                                    @endif  
                                    
                              
                                @endforeach
                                @if ($trovata == -1)
                                    <div id="message5" class="message bg-light p-3 rounded border border-primary text-center w-100" style="color:red;">
                                        <strong>Il file non &egrave; ancora stato caricato -> Obiettivo non raggiunto!</strong>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach

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
