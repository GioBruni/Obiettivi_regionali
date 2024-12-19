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
                        @foreach ($dataView['categorie'] as $categoria)
                            <?php $trovata = false; ?>

                            <!-- Card per Categoria -->
                            <div class="card shadow-sm border-0 mt-2">
                                <div class="card-header bg-primary text-white">
                                    <strong>{{ $categoria->category }}</strong>
                                </div>
                                <div class="card-body">
                                    <?php $trovata = false; ?>
                                    @foreach ($dataView['filesCaricati'] as $index => $file)
                                        @if ($file->target_category_id == $categoria->id)
                                            <?php $trovata = true; ?>

                                            <!-- Stato File -->
                                            @if ($file->category == 'Pre-requisito per il calcolo dell indicatore')
                                                @if ($file->approved === null)
                                                    <!-- Caso: Pre-requisito caricato ma in attesa di approvazione -->
                                                    <div class="message bg-light p-3 rounded border border-warning text-center w-100" style="color: orange;">
                                                        <strong>
                                                            File caricato il:
                                                            {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $file->created_at)->format('d/m/Y H:i') }}
                                                            - Pre-requisito caricato, in attesa di approvazione.
                                                        </strong>
                                                    </div>
                                                @elseif ($file->approved == 0)
                                                    <!-- Caso: Pre-requisito non approvato -->
                                                    <div class="message bg-light p-3 rounded border border-danger text-center w-100" style="color: red;">
                                                        <strong>
                                                            File caricato il:
                                                            {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $file->created_at)->format('d/m/Y H:i') }}
                                                            - Pre-requisito non approvato -> Tutti i punteggi sono azzerati.
                                                        </strong>
                                                    </div>
                                                    @break
                                                @else
                                                    <!-- Caso: Pre-requisito approvato -->
                                                    <div class="message bg-light p-3 rounded border border-success text-center w-100" style="color: green;">
                                                        <strong>
                                                            File caricato il:
                                                            {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $file->created_at)->format('d/m/Y H:i') }}
                                                            - Pre-requisito approvato.
                                                        </strong>
                                                    </div>
                                                @endif
                                            @else
                                                <!-- Caso: File di altre categorie -->
                                                @if ($file->approved === null)
                                                    <!-- File caricato in attesa di approvazione -->
                                                    <div class="message bg-light p-3 rounded border border-warning text-center w-100" style="color: orange;">
                                                        <strong>
                                                            File caricato il:
                                                            {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $file->created_at)->format('d/m/Y H:i') }}
                                                            - In attesa di approvazione.
                                                        </strong>
                                                    </div>
                                                @elseif ($file->approved == 0)
                                                    <div class="message bg-light p-3 rounded border border-danger text-center w-100" style="color: red;">
                                                        <strong>
                                                            File caricato il:
                                                            {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $file->created_at)->format('d/m/Y H:i') }}
                                                            - Non approvato -> Punteggio: 0
                                                        </strong>
                                                    </div>
                                                @else
                                                    <div class="message bg-light p-3 rounded border border-success text-center w-100" style="color: green;">
                                                        <strong>
                                                            File caricato il:
                                                            {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $file->created_at)->format('d/m/Y H:i') }}
                                                            - Approvato -> Punteggio: {{ $dataView['punteggioOb8'][$index] }}
                                                        </strong>
                                                    </div>
                                                @endif
                                            @endif
                                        @endif
                                    @endforeach

                                    @if (!$trovata)
                                        <!-- Caso: Nessun file caricato -->
                                        <div class="message bg-light p-3 rounded border border-primary text-center w-100" style="color: orange;">
                                            <strong>File non ancora caricato -> Obiettivo non raggiunto!</strong>
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

    $(document).ready(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $("#categoriaSelect").change(function () {
            var selectedOption = $(this).find('option:selected');
            var description = selectedOption.attr('data-description');

            $('#descriptionText').text(description);
        });

    });
</script>
@endsection