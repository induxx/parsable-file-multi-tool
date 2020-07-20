<?php

namespace Misery\Component\Source;

use Misery\Component\Decoder\ItemDecoderFactory;
use Misery\Component\Encoder\ItemEncoderFactory;
use Symfony\Component\Yaml\Yaml;

class SourceCollectionFactory
{
    public static function create(ItemEncoderFactory $encoderFactory, ItemDecoderFactory $decoderFactory, array $references, string $bluePrintDir): SourceCollection
    {
        $sources = new SourceCollection('akeneo/csv');

        foreach ($references as $reference => $file) {
            if (is_file($file)) {
                $configuration = self::createConfigurationFromBluePrint($bluePrintDir, 'akeneo/csv', $reference);
                $sources->add(new Source(
                    SourceType::file(),
                    $encoderFactory->createItemEncoder($configuration),
                    $decoderFactory->createItemDecoder($configuration),
                    $file,
                    $reference
                ));
            }
        }

        return $sources;
    }

    private static function createConfigurationFromBluePrint(string $bluePrintDir, string $sourceType, string $reference): array
    {
        $configurationFile = implode(DIRECTORY_SEPARATOR, [
            $bluePrintDir,
            $sourceType,
            $reference .'.yaml'
        ]);

        if (is_file($configurationFile)) {
            return Yaml::parseFile($configurationFile);
        }

        return [];
    }
}