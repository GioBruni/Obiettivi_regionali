<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impostazione password</title>
</head>
<body>
    <h1>Ciao, {{ $data['name'] }}</h1>
    <p>Abbiamo ricevuto una richiesta per impostare una nuova password per il tuo account.<br>Per procedere, ti preghiamo di cliccare sul link qui sotto:</p>

    <p><a href="{{ $data['link'] }}" target="_blank"><input type="button" value="Imposta password" style="display: inline-block; padding: 10px 20px; background-color: #4CAF50; color: white; text-align: center; text-decoration: none; border-radius: 5px;" /></a></p>

    <p>Se non hai richiesto il cambio della password, ignora questa email.</p>
    <p>Ti ricordiamo di scegliere una password sicura che contenga almeno 8 caratteri, una lettera maiuscola, una minuscola, un numero e un carattere speciale (a scelta tra @$!%*#?&).<br>
    Se hai bisogno di assistenza, non esitare a contattarci.</p>
    <p>Saluti,<br>il team di {{ config('app.name') }}</p>
</body>
</html>
