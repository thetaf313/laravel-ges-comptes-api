<?php

namespace App\Traits;

use Illuminate\Http\Response;

trait RestResponse
{
    protected function successResponse($data, $message = '', $pagination = null, $status = Response::HTTP_OK)
    {
        $response = [
            'success' => true,
            'data' => $data,
        ];

        if ($message) {
            $response['message'] = $message;
        }

        if ($pagination) {
            $response['pagination'] = $pagination;
        }

        return response()->json($response, $status);
    }

    protected function errorResponse($message, $code = Response::HTTP_BAD_REQUEST)
    {
        return response()->json([
            'code' => $code,
            'success' => false,
            'error' => $message
        ], $code);
    }

    protected function paginationData($pagination)
    {
        return [
            'currentPage' => $pagination->currentPage(),
            'totalPages' => $pagination->lastPage(),
            'totalItems' => $pagination->total(),
            'itemsPerPage' => $pagination->perPage(),
            'hasNext' => $pagination->hasMorePages(),
            'hasPrevious' => $pagination->currentPage() > 1,
        ];
    }
}
