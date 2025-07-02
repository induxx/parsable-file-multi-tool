<?php

namespace Tests\Misery\Component\Mapping;

use Misery\Component\Mapping\MappingFactory;
use Misery\Component\Common\FileManager\InMemoryFileManager;
use Misery\Component\Configurator\ConfigurationManager;
use Misery\Component\Configurator\Configuration;
use PHPUnit\Framework\TestCase;

class MappingFactoryTest extends TestCase
{
    private $fm;
    private $configStore;
    private $configurationManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fm = $this->createMock(InMemoryFileManager::class);

        $this->configStore = $this->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getMapping', 'addMapping'])
            ->getMock();

        $this->configurationManager = $this->getMockBuilder(ConfigurationManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConfig'])
            ->getMock();
        $this->configurationManager->method('getConfig')
            ->willReturn($this->configStore);
    }

    public function test_it_creates_mapping_from_map_array_and_adds_to_config()
    {
        $mappingName = 'test_mapping';
        $map = [
            'foo' => 'bar',
            'baz' => 'qux',
        ];

        $this->configStore->expects($this->once())
            ->method('getMapping')
            ->with($mappingName)
            ->willReturn(null);
        $this->configStore->expects($this->once())
            ->method('addMapping')
            ->with($mappingName, $map);

        $factory = new MappingFactory();
        $factory->createFromConfiguration([
            [
                'name' => $mappingName,
                'map' => $map,
            ]
        ], $this->fm, $this->configurationManager);
    }

    public function test_it_applies_flip_option()
    {
        $mappingName = 'flip_mapping';
        $map = [
            'foo' => 'bar',
            'baz' => 'qux',
        ];
        $expected = array_flip($map);

        $this->configStore->expects($this->once())
            ->method('getMapping')
            ->with($mappingName)
            ->willReturn(null);
        $this->configStore->expects($this->once())
            ->method('addMapping')
            ->with($mappingName, $expected);

        $factory = new MappingFactory();
        $factory->createFromConfiguration([
            [
                'name' => $mappingName,
                'map' => $map,
                'options' => ['flip'],
            ]
        ], $this->fm, $this->configurationManager);
    }

    public function test_it_applies_flatten_option()
    {
        $mappingName = 'flatten_mapping';
        $map = [
            'foo' => ['bar' => 'baz'],
            'qux' => 'quux',
        ];
        $expected = [
            'foo.bar' => 'baz',
            'qux' => 'quux',
        ];

        $this->configStore->expects($this->once())
            ->method('getMapping')
            ->with($mappingName)
            ->willReturn(null);
        $this->configStore->expects($this->once())
            ->method('addMapping')
            ->with($mappingName, $expected);

        $factory = new MappingFactory();
        $factory->createFromConfiguration([
            [
                'name' => $mappingName,
                'map' => $map,
                'options' => ['flatten'],
            ]
        ], $this->fm, $this->configurationManager);
    }

    public function test_it_applies_values_option()
    {
        $mappingName = 'values_mapping';
        $map = [
            'foo' => 'bar',
            'baz' => 'qux',
        ];
        $expected = array_values($map);

        $this->configStore->expects($this->once())
            ->method('getMapping')
            ->with($mappingName)
            ->willReturn(null);
        $this->configStore->expects($this->once())
            ->method('addMapping')
            ->with($mappingName, $expected);

        $factory = new MappingFactory();
        $factory->createFromConfiguration([
            [
                'name' => $mappingName,
                'map' => $map,
                'options' => ['values'],
            ]
        ], $this->fm, $this->configurationManager);
    }

    public function test_it_applies_keys_option()
    {
        $mappingName = 'keys_mapping';
        $map = [
            'foo' => 'bar',
            'baz' => 'qux',
        ];
        $expected = array_keys($map);

        $this->configStore->expects($this->once())
            ->method('getMapping')
            ->with($mappingName)
            ->willReturn(null);
        $this->configStore->expects($this->once())
            ->method('addMapping')
            ->with($mappingName, $expected);

        $factory = new MappingFactory();
        $factory->createFromConfiguration([
            [
                'name' => $mappingName,
                'map' => $map,
                'options' => ['keys'],
            ]
        ], $this->fm, $this->configurationManager);
    }
}
