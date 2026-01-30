<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Success Response
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @param array $meta
     * @return JsonResponse
     */
    protected function success(mixed $data = [], string $message = 'Action completed', int $statusCode = 200, array $meta = [], ?string $key = null): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'meta'    => $meta,
        ];

        $response[$key ?: 'data'] = $data;

        return response()->json($response, $statusCode);
    }

    /**
     * Error Response
     *
     * @param string $message
     * @param int $statusCode
     * @param array $errors
     * @return JsonResponse
     */
    protected function error(string $message = 'Error occurred', int $statusCode = 400, array $errors = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => [],
            'errors'  => $errors,
        ], $statusCode);
    }
}
