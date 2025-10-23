<?php

namespace App\Exceptions;

use Exception;

class ApiException extends Exception
{
    protected $statusCode;

    public function __construct($message = 'An error occurred', $statusCode = 400, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->statusCode = $statusCode;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function render($request)
    {
        return response()->json([
            'success' => false,
            'error' => $this->getMessage(),
        ], $this->statusCode);
    }
}
