<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificazione MMG</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            width: 90%;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        p {
            text-align: center;
            font-size: 1.1em;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 1em;
        }
        th, td {
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Certificazione MMG</h1>
        @foreach ($tableData as $dati)
            <p><strong>Anno:</strong> {{ $dati->anno }} </p>
            <table>
                <thead>
                    <tr>
                        <th>Totale MMG</th>
                        <th>MMG Coinvolti</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $dati->mmg_totale }}</td> 
                        <td>{{ $dati->mmg_coinvolti }}</td>  
                    </tr>
                </tbody>
            </table>
        @endforeach
    </div>
</body>
</html>
