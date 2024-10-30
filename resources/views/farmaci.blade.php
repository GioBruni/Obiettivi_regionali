@extends('bootstrap-italia::page')

@section('bootstrapitalia_css')
<style>
    .legend {
        font-size: 0.875rem;
        color: #333;
        text-align: left;
        margin-top: 15px;
        border: 1px solid #ddd;
        /* Aggiunta del bordo */
        border-radius: 5px;
        /* Angoli arrotondati per il bordo */
    }

    .legend strong {
        font-weight: bold;
    }
</style>
@endsection

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white text-center">
                    <h4>
                        <i class="fas fa-pills"></i>
                        {{ __('Obiettivo 9: Approvvigionamento farmaci e gestione I ciclo di terapia') }}
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Indicatore 1 -->
                    <div id="GareCUC" class="row justify-content-center">
                        <div class="col-md-12">
                            <div class="card shadow-sm border-0">
                                <div class="card-header bg-primary text-white">
                                    <b>{{ __('Obiettivo 9.1') }}</b>
                                    <br />
                                    <small>{{ __('Garantire il recepimento delle risultanze delle
                                                procedure aggiudicate dalla Centrale Unica di Committenza
                                                della Regione Siciliana entro 10 giorni dalla data di trasmissione
                                                del decreto di aggiudicazione.') }}</small>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <canvas id="fileChart"></canvas>
                                        </div>
                                        <div class="col-md-8 d-flex flex-column align-items-center justify-content-center">
                                            <div class="w-100 mb-3" id="table-container"></div>
                                        </div>
                                    </div>
                                    <div class="legend p-3 border rounded">
                                        <strong>Valori di riferimento</strong><br>
                                        Obiettivo raggiunto al 100% se il risultato è un rapporto pari o superiore a 95%;
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Indicatore 2 -->
                    <div id="PCT" class="row justify-content-center">
                        <div class="col-md-12 mt-4">
                            <div class="card shadow-sm border-0">
                                <div class="card-header bg-primary text-white">
                                    <b>{{ __('Obiettivo 9.2') }}</b>
                                    <br />
                                    <small>{{ __('Ottimizzazione della gestione del I ciclo di terapia a pazienti dimessi sia in DH che in ricovero ordinario.') }}</small>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('farmaci.pct.autocertificazione') }}" method="POST">                                        
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-2">
                                                <label for="year">Anno:</label>
                                                <select id="year" class="form-control" name="year" {{ isset($dataView['PCT']) ? "disabled" : "" }}>
                                                    <?php
                                                    $currentYear = date('Y');
                                                    $startYear = 2024;
                                                    for ($year = $startYear; $year <= $currentYear; $year++) {
                                                        echo "<option value=\"$year\"" . (($year == $currentYear || $dataView['PCT']->year == $year) ? " selected" : "") . ">$year</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label for="begin_month">Mese di inizio:</label>
                                                <select id="begin_month" class="form-control" name="begin_month" {{ isset($dataView['PCT']) ? "disabled" : "" }}>
                                                    <?php
                                                    $mesi = [
                                                        1 => 'Gennaio',
                                                        2 => 'Febbraio',
                                                        3 => 'Marzo',
                                                        4 => 'Aprile',
                                                        5 => 'Maggio',
                                                        6 => 'Giugno',
                                                        7 => 'Luglio',
                                                        8 => 'Agosto',
                                                        9 => 'Settembre',
                                                        10 => 'Ottobre',
                                                        11 => 'Novembre',
                                                        12 => 'Dicembre'
                                                    ];

                                                    foreach ($mesi as $numero => $nome) {
                                                        echo "<option value=\"$numero\"" . (($numero == 1 || $dataView['PCT']->begin_month == $numero ) ? " selected" : "") . ">$nome</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label for="end_month">Mese di fine:</label>
                                                <select id="end_month" class="form-control" name="end_month" {{ isset($dataView['PCT']) ? "disabled" : "" }}>
                                                    <?php
                                                    foreach ($mesi as $numero => $nome) {
                                                        echo "<option value=\"$numero\"" . (($numero == date('n') || $dataView['PCT']->end_month == $numero ) ? " selected" : "") . ">$nome</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="structure_id">Seleziona la struttura:</label>
                                                <select class="form-control" name="structure_id" required id="structure_id" {{ isset($dataView['PCT']) ? "disabled" : "" }}>
                                                    <option value="">-- Seleziona --</option>
                                                    @foreach ($dataView['strutture'] as $rowStruttura)
                                                    <option value="{{ $rowStruttura->id }}" {{ (count($dataView['strutture']) == 1  || $dataView['PCT']->structure_id == $rowStruttura->id ) ? "selected" : "" }}>
                                                        {{ $rowStruttura->name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                                @if ($errors->has('structure_id'))
                                                    <span class="text-danger">{{ $errors->first('structure_id') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="numeratore">Inserire il numeratore</label>
                                                <input type="number" name="numeratore" class="form-control" value="{{ isset($dataView['PCT']) ? $dataView['PCT']->numerator : "" }}"  {{ isset($dataView['PCT']) ? "disabled" : "" }}/>
                                                @if ($errors->has('numeratore'))
                                                    <span class="text-danger">{{ $errors->first('numeratore') }}</span>
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                <label for="denominatore">Inserire il denominatore</label>
                                                <input type="number" name="denominatore" class="form-control" value="{{ isset($dataView['PCT']) ? $dataView['PCT']->denominator : "" }}"  {{ isset($dataView['PCT']) ? "disabled" : "" }} />
                                                @if ($errors->has('denominatore'))
                                                    <span class="text-danger">{{ $errors->first('denominatore') }}</span>
                                                @endif

                                            </div>
                                        </div>
                                        <div class="row" >
                                            <div class="col-md-6">
                                                <label for="file">Scarica autocertificazione da firmare digitalmente</label>
                                                <button type="submit" class="btn btn-primary" {{ isset($dataView['PCT']->uploated_file_id) ? "disabled" : ""}}>Genera autocertificazione</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="card shadow-sm border-0">
                                <div class="card-header bg-primary text-white">
                                    <b>{{ __('Carica l\'autocertificazione firmata digitalmente') }}</b>
                                </div>
                                <div class="card-body">

                                    <div class="row mt-2">
                                        <div class="col-12 col-md-12">

                                            <label for="file">Eseguire la firma elettronica sull'autocertificazione e caricarla nel modulo seguente. Un corretto caricamento assicura che i campi inseriti non siano pi&ugrave; modificabili</label>

                                            <form action="{{ route('farmaci.pct.upload') }}" method="POST" enctype="multipart/form-data">
                                                @csrf

                                                <div class="form-group">
                                                    <input type="file" class="form-control" id="file" name="file" accept=".pdf" required>
                                                </div>
                                                <button type="submit" class="btn btn-primary" {{ isset($dataView['PCT']->uploated_file_id) ? "disabled" : ""}}>Carica autocertificazione</button>

                                            </form>
                                        </div>
                                    </div>
                                    <div class="legend p-3 border rounded">
                                        <strong>Valori di riferimento</strong><br>
                                        Obiettivo raggiunto al 100% se il risultato è un rapporto pari o superiore a 80%.
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


@section('bootstrapitalia_js')
<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    });

    /*
    let charts = {}; // Oggetto per tenere traccia dei grafici
    let nameTable = '';
    let punteggio1 = 0;
    let punteggio2 = 0;

    function fetchChartData(url, anno = '', meseInizio = '', meseFine = '') {
        return $.ajax({
            url: url,
            method: 'GET',
            data: {
                anno: anno,
                mese_inizio: meseInizio,
                mese_fine: meseFine
            },
        }).then(function(response) {
            const data = response;

            console.info('data', data);

            // Inizializza Tabulator
            var hasReparto = data.some(item => item.hasOwnProperty('reparto'));

            // Definisci le colonne dinamicamente
            var columns = [{
                    title: "Presidio",
                    field: "presidio"
                },
                {
                    title: "Numeratore",
                    field: "numeratore",
                    bottomCalc: "sum"
                },
                {
                    title: "Denominatore",
                    field: "denominatore",
                    bottomCalc: "sum"
                },
                {
                    title: "Percentuale",
                    field: "percentuale"
                }
            ];

            // Aggiungi la colonna "Reparto" se è presente nei dati
            if (hasReparto) {
                columns.splice(1, 0, {
                    title: "Reparto",
                    field: "reparto"
                });
            }

            // Crea la Tabulator table
            var table = new Tabulator("#data-table2", {
                data: data,
                layout: "fitColumns",
                columns: columns
            });

            // Calcolo delle somme
            let totale_numeratore = 0;
            let totale_denominatore = 0;

            data.forEach(row => {
                // Usa parseFloat per gestire anche i decimali e controlla se il valore è numerico
                totale_numeratore += parseFloat(row.numeratore) || 0;
                totale_denominatore += parseFloat(row.denominatore) || 0;
            });

            // Calcolo delle percentuali complessive
            let percentuale_totale = totale_denominatore > 0 ? ((totale_numeratore / totale_denominatore) * 100).toFixed(2) : 0;
            if (percentuale_totale >= 80) {
                punteggio2 = 2.5;
            } else {
                punteggio2 = ((percentuale_totale / 80) * 2.5).toFixed(2);
            }

            let riga_finale = {
                Presidio: "Totale",
                totale_numeratore: totale_numeratore,
                totale_denominatore: totale_denominatore,
                percentuale: percentuale_totale,
                punteggio: punteggio2
            };

            // Ritorna il valore finale dalla Promessa


            console.info('riga_finale', riga_finale);
            return riga_finale;
        }).catch(function(error) {
            console.log('Errore nel recupero dei dati', error);
            return {
                totale_numeratore: 0,
                totale_denominatore: 0
            };
        });
    }


    function createDoughnutChart(chartId, data, labels, title) {
        const ctx = document.getElementById(chartId).getContext('2d');

        if (charts[chartId]) {
            charts[chartId].destroy();
        }

        charts[chartId] = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: [100 - data.percentuale, data.percentuale],
                    backgroundColor: ['Red', '#0ec41a'],
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom', // Posiziona la legenda in basso
                        labels: {
                            padding: 35, // Aumenta lo spazio intorno alle etichette della legenda
                            boxWidth: 15, // Larghezza delle caselle di colore
                            font: {
                                size: 14, // Dimensione del font delle etichette della legenda
                            }
                        }
                    },
                    title: {
                        display: true,
                        text: title,
                        font: {
                            size: 14, // Imposta la dimensione del font del titolo
                            weight: 'bold' // Puoi anche cambiare il peso del font
                        },
                        padding: {
                            top: 10,
                            bottom: 30
                        },
                        color: '#333'
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'end',
                        color: '#000', // Colore del testo
                        backgroundColor: 'transparent', // Sfondo trasparente
                        //borderColor: '#000', // Colore della linea di connessione
                        //borderWidth: 1, // Larghezza della linea di connessione
                        //borderRadius: 4,
                        padding: {
                            top: 6,
                            left: 6,
                            right: 6,
                            bottom: 6
                        },
                        formatter: function(value, context) {
                            return value + '%'; // Formattazione dell'etichetta
                        },
                        font: {
                            weight: 'bold',
                            size: 11
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });

        return charts[chartId];
    }

    function updateMessage(elementId, data, thresholds) {
        const messageElement = document.getElementById(elementId);
        if (messageElement) {
            let messageText;
            let messageColor;

            if (elementId == 'message1') {
                if (data.percentuale >= thresholds.full) {
                    messageText = `Pieno raggiungimento dell'obiettivo con punteggio ${data.punteggio}.`;
                    messageColor = 'green';
                } else if (data.percentuale >= thresholds.partial && data.percentuale < thresholds.full) {
                    messageText = `Raggiungimento dell'obiettivo con punteggio ${data.punteggio}.`;
                    messageColor = 'orange';
                } else {
                    messageText = `Raggiungimento dell'obiettivo con punteggio ${data.punteggio}.`;
                    messageColor = 'red';
                }
            } else if (elementId == 'message2') {
                if (data.percentuale >= thresholds.full) {
                    messageText = `Pieno raggiungimento dell'obiettivo con punteggio ${data.punteggio}.`;
                    messageColor = 'green';
                } else if (data.percentuale >= thresholds.partial && data.percentuale < thresholds.full) {
                    messageText = `Raggiungimento dell'obiettivo con punteggio ${data.punteggio}.`;
                    messageColor = 'orange';
                } else {
                    messageText = `Raggiungimento dell'obiettivo con punteggio ${data.punteggio}.`;
                    messageColor = 'red';
                }
            }

            messageElement.innerHTML = `<strong>${messageText}</strong>`;
            messageElement.style.color = messageColor;
        }
    }


    async function updateChart(chartId, messageId, url, labels, title, thresholds, anno = '', meseInizio = '', meseFine = '') {
        const data = await fetchChartData(url, anno, meseInizio, meseFine);
        createDoughnutChart(chartId, data, labels, title);
        updateMessage(messageId, data, thresholds);
    }




    updateChart('pctChart', 'message2', '/farmaci-pct',
        ['PCT non attivati', 'PCT attivati'],
        ['N. prestazioni I ciclo /', 'N. dimessi sia DH che in regime ordinario'], {
            full: 80,
            partial: 60
        }, $('#anno').val(), $('#mese_inizio').val(), $('#mese_fine').val());



    $.ajax({
        url: 'route("gare_chart_data") }}',    
        method: 'GET',
        success: function(data) {
            const numeratore = data.numeratore;
            const denominatore = data.denominatore;
            const rapporto = data.rapporto;
            const risultato = data.risultato;

            // Configurazione del grafico a torta
            const ctx = document.getElementById('fileChart').getContext('2d');
            const fileChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Gare con Entrambi i File', 'Gare Totali'],
                    datasets: [{
                        label: 'Distribuzione dei File',
                        data: [numeratore, denominatore - numeratore],
                        backgroundColor: ['#4caf50', '#f44336'],
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const percentage = denominatore > 0 ? (value / denominatore * 100).toFixed(2) : 0;
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });

            // Configurazione Tabulator
            const table = new Tabulator("#table-container", {
                data: [{
                    numeratore,
                    denominatore,
                    rapporto: rapporto.toFixed(2),
                    risultato
                }],
                layout: "fitColumns",
                columns: [{
                        title: "Numeratore",
                        field: "numeratore",
                        hozAlign: "center"
                    },
                    {
                        title: "Denominatore",
                        field: "denominatore",
                        hozAlign: "center"
                    },
                    {
                        title: "Rapporto",
                        field: "rapporto",
                        hozAlign: "center"
                    },
                    {
                        title: "Risultato",
                        field: "risultato",
                        hozAlign: "center",
                        formatter: "tickCross",
                        formatterParams: {
                            allowEmpty: false,
                            crossElement: false
                        }
                    }
                ]
            });
        },
        error: function(xhr, status, error) {
            console.error('Errore nella richiesta AJAX:', error);
            alert('Si è verificato un errore nel recupero dei dati.');
        }
    });
    */
</script>
@endsection