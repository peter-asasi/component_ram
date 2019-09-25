<?php


namespace by\component\ram;


use Throwable;

class ForbidException extends \Exception
{
    public function __construct(string $message = "permission denied", int $code = 403, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
