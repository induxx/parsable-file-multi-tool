<?php

require_once __DIR__.'/bootstrap.php';

$converter = new Misery\Component\Converter\ItemConverter($encoder, $decoder, $actions);

$converter->convertFromConfigurationFile(__DIR__.'/conversion.yaml');
