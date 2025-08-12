<?php

namespace Tests\Misery\Component\Action;

use Misery\Component\Action\StartFromFieldAction;
use PHPUnit\Framework\TestCase;

class StartFromFieldActionTest extends TestCase
{
    public function test_it_should_return_item_when_from_is_null(): void
    {
        $action = new StartFromFieldAction();

        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
        ];

        $action->setOptions([
            'from' => null,
        ]);

        $this->assertEquals($item, $action->apply($item));
    }

    public function test_it_should_return_field_value_when_from_is_set(): void
    {
        $action = new StartFromFieldAction();

        $item = [
            'brand' => ['louis'],
            'description' => 'LV',
            'sku' => '1',
        ];

        $action->setOptions([
            'from' => 'brand',
        ]);

        $this->assertEquals(['louis'], $action->apply($item));
    }

    public function test_it_should_return_item_when_from_field_does_not_exist(): void
    {
        $action = new StartFromFieldAction();

        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
        ];

        $action->setOptions([
            'from' => 'not_existing',
        ]);

        $this->assertEquals($item, $action->apply($item));
    }
}