<?php

require __DIR__.'/../vendor/autoload.php';

// formatters are reversable // Modifiers are NOT reversable

$modifierRegistry = new \Misery\Component\Common\Registry\Registry('modifier');
$modifierRegistry
    ->register(Misery\Component\Modifier\StripSlashesModifier::NAME, new Misery\Component\Modifier\StripSlashesModifier())
    ->register(Misery\Component\Modifier\ArrayUnflattenModifier::NAME, new Misery\Component\Modifier\ArrayUnflattenModifier())
    ->register(Misery\Component\Modifier\NullifyEmptyStringModifier::NAME, new Misery\Component\Modifier\NullifyEmptyStringModifier())
;

$formatRegistry = new \Misery\Component\Common\Registry\Registry('format');
$formatRegistry
    ->register(Misery\Component\Format\StringToSerializeFormat::NAME, new Misery\Component\Format\StringToSerializeFormat())
    ->register(Misery\Component\Format\StringToFloatFormat::NAME, new Misery\Component\Format\StringToFloatFormat())
    ->register(Misery\Component\Format\StringToIntFormat::NAME, new Misery\Component\Format\StringToIntFormat())
    ->register(Misery\Component\Format\StringToBooleanFormat::NAME, new Misery\Component\Format\StringToBooleanFormat())
    ->register(Misery\Component\Format\StringToDatetimeFormat::NAME, new Misery\Component\Format\StringToDatetimeFormat())
    ->register(Misery\Component\Format\StringToListFormat::NAME, new Misery\Component\Format\StringToListFormat())
;

$actionRegistry = new \Misery\Component\Common\Registry\Registry('action');
$actionRegistry
    ->register(Misery\Component\Actions\RenameAction::NAME, new Misery\Component\Actions\RenameAction())
    ->register(Misery\Component\Actions\RemoveAction::NAME, new Misery\Component\Actions\RemoveAction())
    ->register(Misery\Component\Actions\CopyAction::NAME, new Misery\Component\Actions\CopyAction())
    ->register(Misery\Component\Actions\ReplaceAction::NAME, new Misery\Component\Actions\ReplaceAction())
;

$actions = new \Misery\Component\Actions\ItemActionProcessor($actionRegistry);

$encoder = new \Misery\Component\Encoder\ItemEncoder();
$encoder
    ->addRegistry($formatRegistry)
    ->addRegistry($modifierRegistry)
;