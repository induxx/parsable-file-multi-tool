<?php

namespace Misery\Component\Converter;

interface ReadableInterface
{
    public function read(array $item): false|array;
}