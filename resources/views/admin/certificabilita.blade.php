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

                <div class="card-body">

                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-header bg-primary text-white">
                                <b>{{ __('Certificabilità dei dati e dei bilanci degli Enti del SSR/Certificazione dei Bilanci Aziendali') }}</b>
                        </div>

                        <div class="card-body">                            
                            <div id="istituzione_coordinamento" class="row justify-content-center mt-3">
                                <div class="col-md-12">
                                    <div class="card shadow-sm border-0">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <table class="table table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th>Struttura</th>
                                                                <th>Sub 8.1</th>
                                                                <th>Sub 8.2</th>
                                                                <th>Sub 8.3</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                        @foreach($dataView['files'] as $struttura => $categorie)
                                                            @foreach($categorie as $categoria => $caricato)
                                                            
                                                                @if($categoria == config("constants.TARGET_CATEGORY.OB8_INTERNAL_AUDIT"))
                                                                    @php
                                                                        $sub8_1 = $caricato;
                                                                    @endphp
                                                                @elseif($categoria == config("constants.TARGET_CATEGORY.OB8_RAGG_OBIETTIVI"))
                                                                    @php
                                                                        $sub8_2 = $caricato;
                                                                    @endphp
                                                                @elseif($categoria == config("constants.TARGET_CATEGORY.OB8_BILANCI"))
                                                                    @php
                                                                        $sub8_3 = $caricato;
                                                                    @endphp
                                                                @endif
                                                            @endforeach
                                                            <tr>
                                                                <td>{{ $struttura }} </td>                                            
                                                                <td class="text-white text-center" style="background-color:{{$sub8_1 == 0 ? "red" : "green"}};">{{ $sub8_1 == 0 ? "No" : "Si" }} </td>
                                                                <td class="text-white text-center" style="background-color:{{$sub8_2 == 0 ? "red" : "green"}};">{{ $sub8_2 == 0 ? "No" : "Si" }} </td>
                                                                <td class="text-white text-center" style="background-color:{{$sub8_3 == 0 ? "red" : "green"}};">{{ $sub8_3 == 0 ? "No" : "Si" }} </td>
                                                            </tr>
                                                        @endforeach
                                                        </tbody>
                                                        <caption>
                                                            <div class="it-list-wrapper"><ul class="it-list">
                                                                <li>Sub 8.1 (indicatore al 31/12/2024): Istituzione di una funzione di Internal Audit esclusiva, indipendente, strutturata ed obiettiva finalizzata al miglioramento dell’efficacia e dell’efficienza dell’organizzazione amministrativo contabile aziendale;</li>
                                                                <li>Sub 8.2 (indicatore al 30/06/2025): Piena attuazione delle procedure aziendali adottate e adeguatamente aggiornate dagli Enti del SSR per il raggiungimento degli obiettivi e delle azioni PAC relativi a ciascuno dei cicli e delle Aree di Bilancio.;</li>
                                                                <li>Sub 8.3 (indicatore al 31/12/2025): Certificazione dei Bilanci Aziendali.</li>
                                                            </ul></div>
                                                        </caption>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection