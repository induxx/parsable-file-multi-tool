<?php

namespace Misery\Component\Writer;

use Assert\Assert;
use Misery\Component\Common\FileManager\FileManagerInterface;
use Misery\Component\Common\Redis\RedisItemBufferFactory;
use Misery\Component\Common\Registry\RegisteredByNameInterface;

class ItemWriterFactory implements RegisteredByNameInterface
{
    public function createFromConfiguration(
        array $configuration,
        FileManagerInterface $fileManager
    ) : ItemWriterInterface {
        $type = strtolower($configuration['type']);

        Assert::that(
            $type,
            'type must be filled in.'
        )->notEmpty()->string()->inArray(['xml', 'buffer', 'buffer_csv', 'csv', 'yaml', 'yml', 'xlsx', 'json', 'jsonl', 'redis']);

        if ($type === 'redis') {
            return new RedisWriter(
                RedisItemBufferFactory::create($configuration)
            );
        }

        $filename = $fileManager->provisionPath($configuration['filename']);

        $batchSize = $configuration['batch_size'] ?? 0;
        if ($type === 'xml') {

            if ($batchSize !== 0) {
                return new BatchWriter(
                    XmlStreamWriter::class,
                    $filename,
                    $batchSize,
                        $configuration['options'] ?? []
                );
            }

            return new XmlStreamWriter(
                $filename,
                $configuration['options'] ?? []
            );
        }
        if ($type === 'json' || $type === 'buffer' || $type === 'jsonl') {
            return new JsonlWriter($filename);
        }
        if ($type === 'buffer_csv') {
            $configuration['filename'] = $filename;
            return BufferedCsvWriter::createFromArray($configuration);
        }
        if ($type === 'csv') {
            $configuration['filename'] = $filename;
            return CsvWriter::createFromArray($configuration);
        }
        if ($type === 'yml' || $type === 'yaml') {
            $configuration['filename'] = $filename;
            return new YamlWriter($configuration['filename']);
        }

        if ($type === 'xlsx') {
            $configuration['filename'] = $filename;
            return new XlsxWriter($configuration);
        }

        throw new \RuntimeException('Impossible Exception');
    }

    public function getName(): string
    {
        return 'writer';
    }
}
