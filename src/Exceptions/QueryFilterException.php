<?php

namespace Omalizadeh\QueryFilter\Exceptions;

use Exception;
use Illuminate\Http\Response;

class QueryFilterException extends Exception
{
    public function __construct(
        $message,
        $code = Response::HTTP_UNPROCESSABLE_ENTITY
    ) {
        parent::__construct($message, $code);
        $this->message = $message;
        $this->code = $code;
    }

    public function render(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => $this->message
        ], $this->code);
    }
}
