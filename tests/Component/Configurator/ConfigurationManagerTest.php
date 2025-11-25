<?php

declare(strict_types=1);

namespace Tests\Misery\Component\Configurator;

use Misery\Component\Configurator\ConfigurationManager;
use Misery\Component\Configurator\Configuration;
use Misery\Component\Configurator\ConfigurationFactory;
use Misery\Component\Common\FileManager\LocalFileManager;
use Misery\Component\Common\FileManager\InMemoryFileManager;
use Misery\Component\Common\Collection\ArrayCollection;
use Misery\Component\Common\Pipeline\Pipeline;
use Misery\Component\Source\SourceCollection;
use Misery\Component\Shell\ShellCommands;
use PHPUnit\Framework\TestCase;

class ConfigurationManagerTest extends TestCase
{
    private $config;
    private $factory;
    private $workFiles;
    private $sourceFiles;
    private $sources;

    protected function setUp(): void
    {
        $this->config = $this->createMock(Configuration::class);
        $this->factory = $this->createMock(ConfigurationFactory::class);
        $this->workFiles = $this->createMock(LocalFileManager::class);
        $this->sourceFiles = $this->createMock(LocalFileManager::class);
        $this->sources = $this->createMock(SourceCollection::class);
    }

    public function testConstructionAndGetters(): void
    {
        $manager = new ConfigurationManager(
            $this->config,
            $this->factory,
            $this->workFiles,
            $this->sources,
            $this->sourceFiles
        );
        $this->assertSame($this->config, $manager->getConfig());
        $this->assertSame($this->workFiles, $manager->getWorkFileManager());
        $this->assertInstanceOf(InMemoryFileManager::class, $manager->getInMemoryFileManager());
    }

    public function testAddContextEntryAndAddContext(): void
    {
        $this->config->expects($this->once())
            ->method('setContext')
            ->with('foo', 'bar');
        $this->config->expects($this->once())
            ->method('addContext')
            ->with(['baz' => 'qux']);
        $manager = new ConfigurationManager($this->config, $this->factory, $this->workFiles);
        $manager->addContextEntry('foo', 'bar');
        $manager->addContext(['baz' => 'qux']);
    }

    public function testAddSourcesDelegatesToFactory(): void
    {
        $factoryMock = $this->createMock(ConfigurationFactory::class);
        $sourceFactory = $this->getMockBuilder('stdClass')
            ->addMethods(['createFromConfiguration', 'createFromBluePrint'])
            ->getMock();
        $factoryMock->method('getFactory')->with('source')->willReturn($sourceFactory);
        $sourceFactory->expects($this->once())
            ->method('createFromConfiguration')
            ->willReturn($this->sources);
        $this->config->method('getBlueprints')->willReturn(new ArrayCollection());
        $this->config->expects($this->once())->method('addSources')->with($this->sources);
        $manager = new ConfigurationManager($this->config, $factoryMock, $this->workFiles);
        $manager->addSources([['foo' => 'bar']]);
    }

    public function testConfigureShellCommandsAndPipelines(): void
    {
        $factoryMock = $this->createMock(ConfigurationFactory::class);
        $shellFactory = $this->getMockBuilder('stdClass')->addMethods(['createFromConfiguration'])->getMock();
        $pipelineFactory = $this->getMockBuilder('stdClass')->addMethods(['createFromConfiguration'])->getMock();
        $factoryMock->method('getFactory')->willReturnMap([
            ['shell', $shellFactory],
            ['pipeline', $pipelineFactory],
        ]);
        $shellCommands = new ShellCommands([]);
        $pipeline = $this->createMock(Pipeline::class);
        $shellFactory->expects($this->once())->method('createFromConfiguration')->willReturn($shellCommands);
        $pipelineFactory->expects($this->once())->method('createFromConfiguration')->willReturn($pipeline);
        $this->config->expects($this->once())->method('setShellCommands')->with($shellCommands);
        $this->config->expects($this->once())->method('setPipeline')->with($pipeline);
        $manager = new ConfigurationManager($this->config, $factoryMock, $this->workFiles);
        $manager->configureShellCommands(['foo' => 'bar']);
        $manager->configurePipelines(['baz' => 'qux']);
    }
}
