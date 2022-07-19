<?php

namespace Misery\Component\Statement;

class EmptyStatement implements PredeterminedStatementInterface
{
    public const NAME = 'EMPTY';

    use StatementTrait;

    private function whenField(Field $field, array $item): bool
    {
        if (is_array($item[$field->getField()])) {
            return array_filter($item[$field->getField()]) === [];
        }

        return
            isset($item[$field->getField()]) &&
            empty($item[$field->getField()])
        ;
    }
}