<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AppResponse
{
    public function success($status_code, $status_description, $data = [], $http_status_code = 200): JsonResponse
    {
        $response_data = [
            'status_code' => $status_code,
            'status_description' => $status_description
        ];

        if (!empty($data)) {

            if ($this->isApiResourceCollection($data)) {
                $response_data = array_merge(
                    $response_data,
                    $data->response()->getData(true),
                );
            } else {
                $response_data['data'] = $data;
            }
        }

        return response()->json(
            $response_data,
            $http_status_code
        );
    }

    public function failed($status_code, $status_description, $errors = [], $http_status_code = 200): JsonResponse
    {
        $response_data = [
            'status_code' => $status_code,
            'status_description' => $status_description,
        ];

        if (!empty($errors)) {
            // $errors must be associative array
            // $errors associative array may contain a string or another single array
            $response_data['errors'] = $errors;
        }

        return response()->json(
            $response_data,
            $http_status_code
        );
    }

    private function isApiResourceCollection($data): bool
    {
        return $data instanceof AnonymousResourceCollection;
    }
}
