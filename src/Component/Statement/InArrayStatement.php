<?php

namespace Misery\Component\Statement;

class InArrayStatement implements PredeterminedStatementInterface
{
    public const NAME = 'IN_ARRAY';

    use StatementTrait;

    private function whenField(Field $field, array $item): bool
    {
        $value = $item[$field->getField()] ?? null;
        if (is_null($value)) {
            return false;
        }

        $value = is_array($value) ? $value : [$value];

        return in_array($field->getValue(), $value, true);
    }
}
