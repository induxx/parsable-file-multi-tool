<?php

namespace Misery\Component\Statement;

class RegexStatement implements PredeterminedStatementInterface
{
    public const NAME = 'REGEX';

    use StatementTrait;

    private function whenField(Field $field, array $item): bool
    {
        $field->repopulateFromItem($item);

        return
            isset($item[$field->getField()]) &&
            is_string($item[$field->getField()]) &&
            preg_match($field->getValue(), $item[$field->getField()])
        ;
    }
}