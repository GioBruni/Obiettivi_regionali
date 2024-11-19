<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificazione MMG</title>
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body,
        html {
            font-family: Arial, sans-serif;
            height: 100%;
            /* Ensures full viewport height */
            display: flex;
            flex-direction: column;
            background-color: #fff;
            /* Cambiato a bianco */
        }

        /* Main content container */
        .container {
            width: 90%;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            flex: 1;
            /* Allows container to take up remaining space */
        }

        /* Title and paragraph */
        h1 {
            text-align: center;
            color: #333;
        }

        p {
            text-align: center;
            font-size: 1.1em;
            color: #555;
        }

        /* Table styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 1em;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
            color: #333;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        /* Footer styling */
        footer {
            width: 100%;
            background-color: #fff;
            /* Cambiato a bianco */
            padding: 10px 0;
            text-align: center;
            font-size: 0.9em;
            color: #555;
            position: fixed;
            bottom: 0;
            left: 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Certificazione donazioni</h1>
        @foreach($target6_data as $dati)
            <p><strong>Anno:</strong> {{ $dati->anno }} </p>
            <table>
                <thead>
                    <tr>
                        <th>Totale accertamenti</th>
                        <th>Numero opposti</th>
                        <th>Totale cornee</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $dati->totale_accertamenti }}</td>
                        <td>{{ $dati->numero_opposti }}</td>
                        <td>{{$dati->totale_cornee}}</td>
                    </tr>
                </tbody>
            </table>
        @endforeach
        <br>
    </div>

    <div class="signature-section">
        <div class="signature-right">
            <p><strong>Firma:</strong> ____________________</p>
        </div>
    </div>

    <footer>
        <table style="width: 100%">
            <tr>
                <td>Stampata il {{ date('d/m/Y') }} {{ date('H:i') }}</td>
            </tr>
        </table>
    </footer>
</body>

</html>