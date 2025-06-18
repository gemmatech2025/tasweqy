<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Throwable;
use Illuminate\Http\Request;

class Handler extends ExceptionHandler
{
    // … $levels, $dontReport, $dontFlash remain unchanged …

    public function register(): void
    {
        // Validation exceptions for JSON
        $this->renderable(function (ValidationException $e, Request $request) {
            if (! $request->expectsJson()) {
                return;
            }

            $errors = collect($e->errors())
                ->mapWithKeys(fn($msgs, $field) => [$field => $msgs[0]])
                ->all();

            $message = __('messages.validation_error');
            if ($message === 'messages.validation_error') {
                $message = 'Validation failed';
            }

            if (function_exists('jsonResponse')) {
                return jsonResponse(false, 422, $message, null, null, $errors);
            }

            return response()->json([
                'status'  => false,
                'code'    => 422,
                'message' => $message,
                'errors'  => $errors,
            ], 422);
        });

        // All other JSON exceptions
        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->expectsJson() && function_exists('handleJsonException')) {
                return handleJsonException($e);
            }
        });
    }
}
