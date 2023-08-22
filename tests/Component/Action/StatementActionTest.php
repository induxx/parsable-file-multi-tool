<?php

namespace Tests\Misery\Component\Action;

use Misery\Component\Action\CopyAction;
use Misery\Component\Action\StatementAction;
use PHPUnit\Framework\TestCase;

class StatementActionTest extends TestCase
{
    public function test_it_should_do_a_statement_with_copy_action_and_key_pair_list(): void
    {
        $format = new StatementAction();

        $item = [
            'brand' => 'louis',
            'new_brand' => '',
            'description' => 'LV',
            'sku' => '1',
        ];

        $format->setOptions([
            'when' => [
                'field' => 'brand',
                'operator' => 'IN_LIST',
                'context' => [
                    'list' => [
                        'louis' => null,
                        'nike' => null,
                        'reebok' => null,
                    ],
                ],
            ],
            'then'  => [
                'action' => 'copy',
                'from' => 'brand',
                'to' => 'new_brand',
            ],
        ]);

        $this->assertEquals([
            'brand' => 'louis',
            'new_brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
        ], $format->apply($item));
    }

    public function test_it_should_do_a_statement_with_copy_action_and_list(): void
    {
        $format = new StatementAction();

        $item = [
            'brand' => 'louis',
            'new_brand' => '',
            'description' => 'LV',
            'sku' => '1',
        ];

        $format->setOptions([
            'when' => [
                'field' => 'brand',
                'operator' => 'IN_LIST',
                'context' => [
                    'list' => [
                        'louis',
                        'nike',
                        'reebok',
                    ],
                ],
            ],
            'then'  => [
                'action' => 'copy',
                'from' => 'brand',
                'to' => 'new_brand',
            ],
        ]);

        $this->assertEquals([
            'brand' => 'louis',
            'new_brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
        ], $format->apply($item));
    }
}