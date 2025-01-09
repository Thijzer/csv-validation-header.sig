<?php

namespace Misery\Component\Common\Client\Exception;

class PageNotFoundException extends \Exception
{
    public function __construct(string $message = '404NotFound', int $code = 404, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}