<?php

namespace Misery\Component\Validator\Constraint;

class SnakeCaseConstraint implements Constraint
{
    public const INVALID_FORMAT = 'Invalid format found for snake_case value : %s';
}