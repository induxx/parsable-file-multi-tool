<?php

namespace Misery\Component\Debugger;

class ItemDebugger
{
    public function log($item, $message)
    {
        dump($message, $item);
    }
}