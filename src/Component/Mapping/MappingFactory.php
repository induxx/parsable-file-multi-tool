<?php

namespace Misery\Component\Mapping;

use Misery\Component\Common\Functions\ArrayFunctions;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Configurator\ConfigurationManager;
use Symfony\Component\Yaml\Yaml;

class MappingFactory implements RegisteredByNameInterface
{
    public function createFromConfiguration(array $configuration, string $workingDirectory, ConfigurationManager $configurationManager)
    {
        foreach ($configuration as $mappingList) {
            if (isset($mappingList['name'])) {
                $mapping = $configurationManager->getConfig()->getMapping($mappingList['name']);
                if (null === $mapping && isset($mappingList['source'])) {
                    $mapping = $this->create($workingDirectory . DIRECTORY_SEPARATOR . $mappingList['source']);
                    if (isset($mappingList['options'])) {
                        if (in_array('flatten', $mappingList['options'])) {
                            $mapping = ArrayFunctions::flatten($mapping);
                        }
                        if (in_array('flip', $mappingList['options'])) {
                            $mapping = array_flip($mapping);
                        }
                    }
                }
                if (null === $mapping && isset($mappingList['sets'])) {
                    $mapping = array_merge(...array_map(function ($mappingName) use ($configurationManager) {
                        return $configurationManager->getConfig()->getMapping($mappingName);
                    }, $mappingList['sets']));
                }

                $configurationManager->getConfig()->addMapping($mappingList['name'], $mapping);
            }
        }
    }

    private function create($filename)
    {
        return Yaml::parseFile($filename);
    }

    public function getName(): string
    {
        return 'mapping';
    }
}