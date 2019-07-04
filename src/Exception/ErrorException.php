<?php

namespace James\SolarCode\Exception;

use Exception;
use Throwable;

class ErrorException extends Exception
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
