<?php

namespace Misery\Component\Source;

use Misery\Component\Encoder\ItemEncoder;

class SourceCollectionFactory
{
    public static function create(ItemEncoder $encoder, array $references, string $akeneoBluePrintDir): SourceCollection
    {
        $sources = new \Misery\Component\Source\SourceCollection('akeneo/csv', $encoder, $akeneoBluePrintDir);

        foreach ($references as $reference => $file) {
            if (is_file($file)) {
                $sources->add(\Misery\Component\Source\Source::promise(
                    \Misery\Component\Source\SourceType::file(),
                    $file,
                    $reference
                ));
            }
        }

        return $sources;
    }
}