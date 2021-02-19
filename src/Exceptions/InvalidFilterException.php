<?php

namespace Omalizadeh\QueryFilter\Exceptions;

use Exception;
use Illuminate\Http\Request;

class InvalidFilterException extends Exception
{
    protected $message;
    protected $code;

    public function __construct(
        $message,
        $code = 422
    ) {
        parent::__construct($message, $code);
        $this->message = $message;
        $this->code = $code;
    }

    public function render(Request $request)
    {
        return response()->json([
            'message' => $this->message
        ], $this->code);
    }
}
