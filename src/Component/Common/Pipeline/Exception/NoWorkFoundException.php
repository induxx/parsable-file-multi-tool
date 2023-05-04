<?php

namespace Misery\Component\Common\Pipeline\Exception;

class NoWorkFoundException extends \InvalidArgumentException
{
    public function __construct(int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct('No work found!', $code, $previous);
    }
}