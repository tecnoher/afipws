<?php

namespace AfipWS\Error;

class RuntimeErrorException extends CustomException
{
    public function __construct($title, $message, $code = 560, \Exception $previous = null)
    {
        parent::__construct($title, $message, $code, $previous);
    }
}
