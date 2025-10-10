<?php

namespace Tests\Misery\Component\Mapping;

use Misery\Component\Mapping\ColumnMapper;
use PHPUnit\Framework\TestCase;

class ColumnMapperTest extends TestCase
{
    public function test_it_should_map_column_names(): void
    {
        $mapper = new ColumnMapper();

        $mappings = [
            'Code' => 'code',
            'Wassen' => 'wash',
            'Hi Temp' => 'hi_temp',
        ];

        $data = [
            'Code' => '1',
            'Wassen' => 'B',
            'not_mapped' => 'C',
        ];

        $expected = [
            'code'=> '1',
            'wash' => 'B',
            'not_mapped' => 'C',
        ];

        $this->assertSame($expected, $mapper->map($data, $mappings));
    }

    public function test_it_should_thrown_exception_if_column_names_are_wrong_mapped(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $mapper = new ColumnMapper();

        $mappings = [
            'Temp' => 'temperature'
        ];

        $data = [
            'code' => '1',
            'Wassen' => 'B'
        ];

        $mapper->map($data, $mappings);
    }

    public function test_it_should_not_throw_exception_if_strict_mode_is_false(): void
    {
        $mapper = new ColumnMapper(strictMode: false);

        $mappings = [
            'Temp' => 'temperature',
            'Extra' => 'extra_column',
        ];

        $data = [
            'code' => '1',
            'Wassen' => 'B',
        ];

        // Should not throw, and should return original keys since mappings are missing
        $expected = [
            'code' => '1',
            'Wassen' => 'B',
        ];
        $this->assertSame($expected, $mapper->map($data, $mappings));
    }

    public function test_it_should_throw_exception_if_strict_mode_is_true(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $mapper = new ColumnMapper(strictMode: true);
        $mappings = [
            'Temp' => 'temperature',
        ];
        $data = [
            'code' => '1',
            'Wassen' => 'B',
        ];
        $mapper->map($data, $mappings);
    }
}
