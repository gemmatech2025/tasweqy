<?php
// app/Exceptions/handleJsonException.php

use Illuminate\Http\Exceptions\HttpResponseException;      // ← guard this first
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\{
    HttpException,
    NotFoundHttpException,
    MethodNotAllowedHttpException,
    AccessDeniedHttpException,
    TooManyRequestsHttpException
};
use Illuminate\Auth\{
    AuthenticationException,
    Access\AuthorizationException
};
use Illuminate\Http\Exceptions\ThrottleRequestsException;

if (! function_exists('handleJsonException')) {
    /**
     * Convert any Throwable into a standardized JSON response.
     */
    function handleJsonException(\Throwable $exception)
    {
        // 1) If this is an HttpResponseException (e.g. from failedValidation),
        //    just return its JSON response directly:
        if ($exception instanceof HttpResponseException) {
            return $exception->getResponse();
        }

        // 2) Otherwise build your usual envelope:
        $status  = false;
        $code    = 500;
        $message = __('messages.server_error') ?: 'Something went wrong.';
        $errors  = null;

        if ($exception instanceof ValidationException) {
            $code     = 422;
            $message  = __('messages.validation_error') ?: 'Validation failed.';
            $errors   = $exception->errors();
        }
        elseif ($exception instanceof NotFoundHttpException) {
            $code    = 404;
            $message = $exception->getMessage()
                        ?: __('messages.route_not_found')
                        ?: 'Route not found.';
        }
        elseif ($exception instanceof MethodNotAllowedHttpException) {
            $code    = 405;
            $message = __('messages.method_not_allowed') ?: 'HTTP method not allowed.';
        }
        elseif ($exception instanceof TooManyRequestsHttpException
             || $exception instanceof ThrottleRequestsException) {
            $code    = 429;
            $message = __('messages.too_many_requests')
                        ?: 'Too many requests. Please try again.';
        }
        elseif ($exception instanceof AuthenticationException) {
            $code    = 401;
            $message = __('messages.unauthenticated') ?: 'You must be logged in.';
        }
        elseif ($exception instanceof AuthorizationException
             || $exception instanceof AccessDeniedHttpException) {
            $code    = 403;
            $message = __('messages.unauthorized') ?: 'You do not have access to this resource.';
        }
        elseif ($exception instanceof HttpException) {
            $code    = $exception->getStatusCode();
            $message = $exception->getMessage()
                        ?: __('messages.http_error')
                        ?: 'HTTP error occurred.';
        }
        elseif ($exception instanceof QueryException) {
            $code    = 500;
            $message = __('messages.database_error') ?: 'Database error occurred.';
            $errors  = [
                'sql'      => $exception->getSql(),
                'bindings' => $exception->getBindings(),
                'error'    => $exception->getMessage(),
            ];
        }
        else {
            // Fallback: include debug info in errors
            $errors = [
                'exception' => get_class($exception),
                'message'   => $exception->getMessage(),
                'file'      => $exception->getFile(),
                'line'      => $exception->getLine(),
            ];
        }

        return jsonResponse($status, $code, $message, null, null, $errors);
    }
}
