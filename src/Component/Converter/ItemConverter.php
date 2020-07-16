<?php

namespace Misery\Component\Converter;

use Misery\Component\Actions\ItemActionProcessor;
use Misery\Component\Encoder\ItemEncoder;
use Misery\Component\Source\CreateSourcePaths;
use Misery\Component\Source\SourceCollectionFactory;
use Misery\Component\Writer\CsvWriter;
use Symfony\Component\Yaml\Yaml;

class ItemConverter
{
    public static function convertFromConfigurationFile(ItemEncoder $encoder, ItemActionProcessor $actions, string $configuration): void
    {
        $rootDir = '/app';
        $configuration = Yaml::parseFile($configuration);
        $akeneoBluePrintDir = $rootDir.'/src/BluePrint';

        // blend client configuration and customer configuration

        $actions->setContext($sources = SourceCollectionFactory::create($encoder, CreateSourcePaths::create(
            $configuration['sources'],
            $rootDir.'/examples/akeneo/icecat_demo_dev/%s.csv'
        ), $akeneoBluePrintDir), $configuration['conversion']['actions'] ?? []);

        $data = $sources->get($configuration['conversion']['data']['source'])->getReader();

        $writer = CsvWriter::createFromArray($configuration['conversion']['output']['writer']);
        $writer->clear();

        // decoder needs to happen before write
        foreach ($data->getIterator() as $item) {
            $writer->write($actions->process($item));
        }
    }
}