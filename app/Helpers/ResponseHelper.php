<?php

if (!function_exists('jsonResponse')) {
    /**
     * Returns a standardized JSON response.
     *
     * @param bool $status
     * @param int $code
     * @param string|null $message
     * @param mixed $data
     * @param array|null $meta
     * @param array|null $errors
     * @return \Illuminate\Http\JsonResponse
     */
    function jsonResponse($status, $code, $message = null, $data = null, $meta = null, $errors = null)
    {
        $response = [
            'status' => $status,
            'code' => $code,
            'message' => $message,
        ];

        if (!is_null($data)) {
            $response['data'] = $data;
        }

        if (!$status && !is_null($errors)) {
            $response['errors'] = $errors;
        }
        
        if (!is_null($meta)) {
            $response['meta'] = $meta;
        }


        return response()->json($response, $code);
    }
}
