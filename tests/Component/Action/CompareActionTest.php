<?php

namespace Tests\Misery\Component\Action;

use Misery\Component\Action\CompareAction;
use Misery\Component\Common\Pipeline\Exception\SkipPipeLineException;
use PHPUnit\Framework\TestCase;

class CompareActionTest extends TestCase
{
    public function testEqualsComparisonWithPrimitiveValuesMatch(): void
    {
        $action = new CompareAction();
        $action->setOptions([
            'field' => 'field1',
            'compare_field' => 'field2',
            'state' => 'EQUALS',
            'then' => [
                'action' => 'skip',
                'skip_message' => 'Velden zijn gelijk'
            ]
        ]);

        $item = ['field1' => 'waarde', 'field2' => 'waarde'];

        $this->expectException(SkipPipeLineException::class);
        $this->expectExceptionMessage('Velden zijn gelijk');

        $action->apply($item);
    }

    public function testEqualsComparisonWithPrimitiveValuesNoMatch(): void
    {
        $action = new CompareAction();
        $action->setOptions([
            'field' => 'field1',
            'compare_field' => 'field2',
            'state' => 'EQUALS',
            'then' => [
                'action' => 'skip',
                'skip_message' => 'Velden zijn gelijk'
            ]
        ]);

        $item = ['field1' => 'waarde1', 'field2' => 'waarde2'];
        $result = $action->apply($item);

        $this->assertEquals($item, $result);
    }

    public function testNotEqualsComparisonWithPrimitiveValuesMatch(): void
    {
        $action = new CompareAction();
        $action->setOptions([
            'field' => 'field1',
            'compare_field' => 'field2',
            'state' => 'NOT_EQUALS',
            'then' => [
                'action' => 'skip',
                'skip_message' => 'Velden zijn niet gelijk'
            ]
        ]);

        $item = ['field1' => 'waarde1', 'field2' => 'waarde2'];

        $this->expectException(SkipPipeLineException::class);
        $this->expectExceptionMessage('Velden zijn niet gelijk');

        $action->apply($item);
    }

    public function testNotEqualsComparisonWithPrimitiveValuesNoMatch(): void
    {
        $action = new CompareAction();
        $action->setOptions([
            'field' => 'field1',
            'compare_field' => 'field2',
            'state' => 'NOT_EQUALS',
            'then' => [
                'action' => 'skip',
                'skip_message' => 'Velden zijn niet gelijk'
            ]
        ]);

        $item = ['field1' => 'waarde', 'field2' => 'waarde'];
        $result = $action->apply($item);

        $this->assertEquals($item, $result);
    }

    public function testEqualsComparisonWithArraysMatch(): void
    {
        $action = new CompareAction();
        $action->setOptions([
            'field' => 'arr1',
            'compare_field' => 'arr2',
            'state' => 'EQUALS',
            'then' => [
                'action' => 'copy',
                'from' => 'src',
                'to' => 'dest'
            ]
        ]);

        $item = [
            'arr1' => [3, 2, 1],
            'arr2' => [1, 2, 3],
            'src' => 'waarde'
        ];
        $result = $action->apply($item);

        $this->assertArrayHasKey('dest', $result);
        $this->assertEquals('waarde', $result['dest']);
    }

    public function testEqualsComparisonWithArraysDifferentLength(): void
    {
        $action = new CompareAction();
        $action->setOptions([
            'field' => 'arr1',
            'compare_field' => 'arr2',
            'state' => 'EQUALS',
            'then' => [
                'action' => 'copy',
                'from' => 'src',
                'to' => 'dest'
            ]
        ]);

        $item = [
            'arr1' => [1, 2, 3],
            'arr2' => [1, 2],
            'src' => 'waarde'
        ];
        $result = $action->apply($item);

        $this->assertArrayNotHasKey('dest', $result);
        $this->assertEquals($item, $result);
    }

    public function testEqualsComparisonWithArraysDifferentContent(): void
    {
        $action = new CompareAction();
        $action->setOptions([
            'field' => 'arr1',
            'compare_field' => 'arr2',
            'state' => 'EQUALS',
            'then' => [
                'action' => 'copy',
                'from' => 'src',
                'to' => 'dest'
            ]
        ]);

        $item = [
            'arr1' => [1, 2, 3],
            'arr2' => [1, 2, 4],
            'src' => 'waarde'
        ];
        $result = $action->apply($item);

        $this->assertArrayNotHasKey('dest', $result);
        $this->assertEquals($item, $result);
    }

    public function testNotEqualsComparisonWithArraysDifferentContent(): void
    {
        $action = new CompareAction();
        $action->setOptions([
            'field' => 'arr1',
            'compare_field' => 'arr2',
            'state' => 'NOT_EQUALS',
            'then' => [
                'action' => 'copy',
                'from' => 'src',
                'to' => 'dest'
            ]
        ]);

        $item = [
            'arr1' => [1, 2, 3],
            'arr2' => [1, 2, 4],
            'src' => 'waarde'
        ];
        $result = $action->apply($item);

        $this->assertArrayHasKey('dest', $result);
        $this->assertEquals('waarde', $result['dest']);
    }

    public function testNotEqualsComparisonWithArraysDifferentLength(): void
    {
        $action = new CompareAction();
        $action->setOptions([
            'field' => 'arr1',
            'compare_field' => 'arr2',
            'state' => 'NOT_EQUALS',
            'then' => [
                'action' => 'copy',
                'from' => 'src',
                'to' => 'dest'
            ]
        ]);

        $item = [
            'arr1' => [1, 2, 3],
            'arr2' => [1, 2],
            'src' => 'waarde'
        ];
        $result = $action->apply($item);

        $this->assertArrayHasKey('dest', $result);
        $this->assertEquals('waarde', $result['dest']);
    }

    public function testWithMissingField(): void
    {
        $action = new CompareAction();
        $action->setOptions([
            'field' => 'nietAanwezig',
            'compare_field' => 'field2',
            'state' => 'EQUALS',
            'then' => [
                'action' => 'skip'
            ]
        ]);

        $item = ['field2' => 'waarde'];
        $result = $action->apply($item);

        $this->assertEquals($item, $result);
    }

    public function testWithDifferentTypes(): void
    {
        $action = new CompareAction();
        $action->setOptions([
            'field' => 'field1',
            'compare_field' => 'field2',
            'state' => 'EQUALS',
            'then' => [
                'action' => 'skip'
            ]
        ]);

        $item = ['field1' => 123, 'field2' => '123'];
        $result = $action->apply($item);

        $this->assertEquals($item, $result);
    }

    public function testWithEmptyThenOption(): void
    {
        $action = new CompareAction();
        $action->setOptions([
            'field' => 'field1',
            'compare_field' => 'field2',
            'state' => 'EQUALS',
            'then' => []
        ]);

        $item = ['field1' => 'waarde', 'field2' => 'waarde'];
        $result = $action->apply($item);

        $this->assertEquals($item, $result);
    }

    public function testWithUnsupportedAction(): void
    {
        $action = new CompareAction();
        $action->setOptions([
            'field' => 'field1',
            'compare_field' => 'field2',
            'state' => 'EQUALS',
            'then' => [
                'action' => 'onbekend'
            ]
        ]);

        $item = ['field1' => 'waarde', 'field2' => 'waarde'];
        $result = $action->apply($item);

        $this->assertEquals($item, $result);
    }

    public function testDestructShouldClearValues(): void
    {
        $action = new CompareAction();

        $reflection = new \ReflectionClass($action);
        $property = $reflection->getProperty('values');
        $property->setAccessible(true);
        $property->setValue($action, ['test' => 'waarde']);

        $action->__destruct();

        $this->assertEmpty($property->getValue($action));
    }
}