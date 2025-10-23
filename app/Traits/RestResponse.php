<?php

namespace App\Traits;

trait RestResponse
{
    protected function successResponse($data, $message = '', $pagination = null)
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

        return response()->json($response, 200);
    }

    protected function errorResponse($message, $code = 400)
    {
        return response()->json([
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
