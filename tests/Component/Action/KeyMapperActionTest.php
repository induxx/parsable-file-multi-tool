<?php

namespace Tests\Misery\Component\Action;
use Misery\Component\Action\KeyMapperAction;
use Misery\Component\Converter\Matcher;
use PHPUnit\Framework\TestCase;

class KeyMapperActionTest extends TestCase
{
    public function testApplyWithListMapping()
    {
        $item = ['key1' => 'value1', 'key2' => 'value2'];

        $action = new KeyMapperAction();
        $action->setOptions([
            'list' => ['key1' => 'new_key1', 'key2' => 'new_key2']
        ]);

        $result = $action->apply($item);

        $this->assertEquals(['new_key1' => 'value1', 'new_key2' => 'value2'], $result);
    }

    public function testApplyWithMatcherMapping()
    {
        $item = [
            'values|key1' => [
                'matcher' => Matcher::create('key1'),
                'data' => 'value1',
            ],
            'values|key2' => [
                'matcher' => Matcher::create('key2'),
                'data' => 'value2',
            ],
            'key3' => 'value3',
        ];

        $action = new KeyMapperAction();
        $action->setOptions([
            'list' => ['key2' => 'new_key2']
        ]);

        $result = $action->apply($item);

        $this->assertEquals([
            'values|key1' => [
                'matcher' => Matcher::create('key1'),
                'data' => 'value1',
            ],
            'new_key2' => [
                'matcher' => Matcher::create('key2'),
                'data' => 'value2',
            ],
            'key3' => 'value3',
        ], $result);
    }

    public function testApplyWithMatcherMappingInReverse()
    {
        $item = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ];

        $action = new KeyMapperAction();
        $action->setOptions([
            'list' => ['new_key2' => 'key2'],
            'reverse' => true,
        ]);

        $result = $action->apply($item);

        $this->assertEquals([
            'key1' => 'value1',
            'new_key2' => 'value2',
            'key3' => 'value3',
        ], $result);
    }

    public function testApplyWithMatcherMappingInReverseWithMatcher()
    {
        $item = [
            'values|key1' => [
                'matcher' => Matcher::create('key1'),
                'data' => 'value1',
            ],
            'values|key2' => [
                'matcher' => Matcher::create('key2'),
                'data' => 'value2',
            ],
            'key3' => 'value3',
        ];

        $action = new KeyMapperAction();
        $action->setOptions([
            'list' => ['values|new_key2' => 'values|key2'],
            'reverse' => true,
        ]);

        $result = $action->apply($item);

        $this->assertEquals([
            'values|key1' => [
                'matcher' => Matcher::create('key1'),
                'data' => 'value1',
            ],
            'key3' => 'value3',
            'values|new_key2' => [
                'matcher' => Matcher::create('key2'),
                'data' => 'value2',
            ],
        ], $result);
    }

    public function testApplyWithAllowJoinMergesStringValues()
    {
        $item = [
            'A' => 'foo',
            'B' => 'bar',
            'C' => 'baz',
        ];

        $action = new KeyMapperAction();
        $action->setOptions([
            'list' => [
                'A' => 'X',
                'B' => 'X',
                'C' => 'Y',
            ],
            'allow_join' => true,
            'separator' => ',',
        ]);

        $result = $action->apply($item);

        $this->assertEquals([
            'X' => 'foo,bar',
            'Y' => 'baz',
        ], $result);
    }

    public function testApplyWithAllowJoinSkipsEmptyStrings()
    {
        $item = [
            'A' => '',
            'B' => 'bar',
            'C' => '',
        ];

        $action = new KeyMapperAction();
        $action->setOptions([
            'list' => [
                'A' => 'X',
                'B' => 'X',
                'C' => 'X',
            ],
            'allow_join' => true,
            'separator' => ';',
        ]);

        $result = $action->apply($item);

        $this->assertEquals([
            'X' => 'bar',
        ], $result);
    }

    public function testApplyWithAllowJoinSkipsNonStringValues()
    {
        $item = [
            'A' => 123,
            'B' => 'bar',
            'C' => null,
            'D' => ['array'],
        ];

        $action = new KeyMapperAction();
        $action->setOptions([
            'list' => [
                'A' => 'X',
                'B' => 'X',
                'C' => 'X',
                'D' => 'X',
            ],
            'allow_join' => true,
            'separator' => '|',
        ]);

        $result = $action->apply($item);

        $this->assertEquals([
            'X' => 'bar',
        ], $result);
    }

    public function testApplyReturnsOriginalItemWhenNoReverseNoAllowJoinAndEmptyList()
    {
        $item = [
            'foo' => 'bar',
            'baz' => 'qux',
        ];

        $action = new KeyMapperAction();
        $action->setOptions([
            'list' => [],
            'reverse' => false,
            'allow_join' => false,
        ]);

        $result = $action->apply($item);

        $this->assertSame($item, $result);
    }
}
