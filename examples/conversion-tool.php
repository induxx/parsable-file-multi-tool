<?php

require_once __DIR__.'/bootstrap.php';

Misery\Component\Converter\ItemConverter::convertFromConfigurationFile(
    $encoder, $actions, __DIR__.'/conversion.yaml'
);
