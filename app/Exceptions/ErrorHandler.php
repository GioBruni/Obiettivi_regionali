<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class ErrorHandler extends ExceptionHandler
{
    protected $dontReport = [
        // Qui puoi elencare le eccezioni che non vuoi siano riportate nei log
    ];

    /**
     * Una lista delle eccezioni che dovrebbero essere trasformate in una risposta HTTP valida.
     *
     * @var array<class-string<\Throwable>>
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Registra eventuali callback per la gestione delle eccezioni.
     */
    public function register()
    {
        $this->renderable(function (Throwable $e, $request) {
            // Controlla se l'eccezione è del tipo che vuoi catturare
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return response()->view('errors.not-found', [], 404);
            }

            // Altrimenti, puoi restituire un messaggio generico di cortesia
            return response()->view('errors.generic-error', [], 500);
        });
    }

    protected function errorResponse($message, $code)
    {
        return response()->view('errors.generic-error', [], 500);
    }

    public function render($request, Throwable $exception)
    {
        return $this->errorResponse($exception->getMessage(), 500);

    }
}
