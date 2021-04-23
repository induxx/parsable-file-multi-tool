<?php

namespace Misery\Component\Writer;

use Assert\Assert;
use Misery\Component\Common\Registry\RegisteredByNameInterface;

class ItemWriterFactory implements RegisteredByNameInterface
{
    public function createFromConfiguration(
        array $configuration,
        string $workingDirectory
    ) : ItemWriterInterface {
        Assert::that(
            $configuration['type'],
            'type must be filled in.'
        )->notEmpty()->string()->inArray(['xml', 'csv']);

        if ($configuration['type'] === 'xml') {
            return new XmlWriter(
                $workingDirectory . DIRECTORY_SEPARATOR . $configuration['filename'],
                $configuration['options'] ?? []
            );
        }
        if ($configuration['type'] === 'csv') {
            return new CsvWriter(
                $workingDirectory . DIRECTORY_SEPARATOR . $configuration['filename'],
                $configuration['options'] ?? []
            );
        }

        throw new \RuntimeException('Impossible Exception');
    }

    public function getName(): string
    {
        return 'writer';
    }
}