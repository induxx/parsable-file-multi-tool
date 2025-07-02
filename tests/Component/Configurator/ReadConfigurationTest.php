<?php

namespace Tests\Misery\Component\Configurator;

use Misery\Component\Common\Collection\ArrayCollection;
use Misery\Component\Configurator\ReadOnlyConfiguration;
use PHPUnit\Framework\TestCase;
use Misery\Component\Configurator\Configuration;
use Misery\Component\Common\Client\ApiClientInterface;
use Misery\Component\Common\FileManager\LocalFileManager;
use Misery\Component\Source\SourceCollection;

class ReadConfigurationTest extends TestCase
{
    private function createConf(): Configuration
    {
        $configuration = new Configuration();

        $fm = $this->createMock(LocalFileManager::class);
        $configuration->setWorkFm($fm);

        return $configuration;
    }

    public function testSetAndGetWorkFm()
    {
        $configuration = $this->createConf();

        $readConfig = ReadOnlyConfiguration::loadFromConfiguration($configuration);

        $this->assertSame($configuration->getWorkFm(), $readConfig->getWorkFm());
    }

    public function testAddAndGetAccount()
    {
        $configuration = $this->createConf();
        $apiClient = $this->createMock(ApiClientInterface::class);

        $configuration->addAccount('test', $apiClient);
        $readConfig = ReadOnlyConfiguration::loadFromConfiguration($configuration);

        $this->assertSame($apiClient, $readConfig->getAccount('test'));
        $this->assertNull($readConfig->getAccount('nonexistent'));
    }

    public function testAddAndGetSources()
    {
        $configuration = $this->createConf();
        $sourceCollection = $this->createMock(SourceCollection::class);

        $configuration->addSources($sourceCollection);
        $readConfig = ReadOnlyConfiguration::loadFromConfiguration($configuration);

        $this->assertSame($sourceCollection, $readConfig->getSources());
    }

    public function testAddAndGetLists()
    {
        $configuration = $this->createConf();

        // Adding lists
        $list1 = ['item1', 'item2', 'item3'];
        $list2 = ['itemA', 'itemB', 'itemC'];

        $configuration->addList('list1', $list1);
        $configuration->addList('list2', $list2);

        // Retrieving lists
        $lists = $configuration->getLists();

        $this->assertCount(2, $lists);
        $this->assertArrayHasKey('list1', $lists);
        $this->assertArrayHasKey('list2', $lists);

        $collection1 = new ArrayCollection($list1);
        $collection2 = new ArrayCollection($list2);
        $this->assertEquals($collection1, $lists['list1']);
        $this->assertEquals($collection2, $lists['list2']);

        // Updating a list
        $updatedList = ['itemX', 'itemY', 'itemZ'];
        $configuration->updateList('list1', $updatedList);

        $readConfig = ReadOnlyConfiguration::loadFromConfiguration($configuration);
        $updatedLists = $readConfig->getLists();

        // Retrieving a single list
        $retrievedList = $readConfig->getList('list1');
        $this->assertEquals($updatedLists['list1'], $retrievedList);

        // Non-existent list
        $nonExistentList = $readConfig->getList('nonexistent');
        $this->assertNull($nonExistentList);
    }
}