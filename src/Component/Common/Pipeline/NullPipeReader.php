<?php

namespace Misery\Component\Common\Pipeline;

class NullPipeReader implements PipeReaderInterface
{
    public function read()
    {
        return false;
    }

    public function stop(): void
    {
    }
}