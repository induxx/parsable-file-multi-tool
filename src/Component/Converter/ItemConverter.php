<?php

namespace Misery\Component\Converter;

use Misery\Component\Action\ItemActionProcessorFactory;
use Misery\Component\Decoder\ItemDecoderFactory;
use Misery\Component\Encoder\ItemEncoderFactory;
use Misery\Component\Source\CreateSourcePaths;
use Misery\Component\Source\SourceCollectionFactory;
use Misery\Component\Writer\CsvWriter;
use Symfony\Component\Yaml\Yaml;

class ItemConverter
{
    /**
     * @var ItemEncoderFactory
     */
    private $encoderFactory;
    /**
     * @var ItemDecoderFactory
     */
    private $decoderFactory;
    /**
     * @var ItemActionProcessorFactory
     */
    private $actionFactory;

    public function __construct(ItemEncoderFactory $encoderFactory, ItemDecoderFactory $decoderFactory, ItemActionProcessorFactory $actionFactory)
    {
        $this->encoderFactory = $encoderFactory;
        $this->decoderFactory = $decoderFactory;
        $this->actionFactory = $actionFactory;
    }

    public function convertFromConfigurationFile(string $configuration): void
    {
        $rootDir = '/app';
        $bluePrintDir = $rootDir.'/src/BluePrint';
        $configuration = Yaml::parseFile($configuration);

        // blend client configuration and customer configuration

        $actionProcessor = $this->actionFactory->createActionProcessor($sources = SourceCollectionFactory::create($this->encoderFactory, $this->decoderFactory, CreateSourcePaths::create(
            $configuration['sources'],
            $rootDir.'/examples/akeneo/icecat_demo_dev/%s.csv'
        ), $bluePrintDir), $configuration['conversion']['actions'] ?? []);

        $source = $sources->get($configuration['conversion']['data']['source']);

        $writer = CsvWriter::createFromArray($configuration['conversion']['output']['writer']);
        $writer->clear();

        // decoder needs to happen before write
        foreach ($source->getReader()->getIterator() as $item) {
            $writer->write($source->decode($actionProcessor->process($item)));
        }
    }
}