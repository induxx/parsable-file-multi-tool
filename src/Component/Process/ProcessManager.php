<?php

namespace Misery\Component\Process;

use Misery\Component\Common\Pipeline\LoggingPipe;
use Misery\Component\Configurator\Configuration;

class ProcessManager
{
    private Configuration $configuration;
    private ?int $startTimeStamp = null;
    private ?int $invalidItems = 0;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    private function log(string $message)
    {
        $this->configuration->getLogger()->info($message);
    }

    public function startProcess(): void
    {
        $this->startTimeStamp = microtime(true);
        $this->log(sprintf("Running Step :: %s ", basename($this->configuration->getContext('transformation_file'))));

        $debug = $this->configuration->getContext('debug');
        $line = $this->configuration->getContext('line') ?? -1;
        $amount = $this->configuration->getContext('try');
        $mappings = $this->configuration->getContext('show_mappings');
        if ($line !== -1) {
            $debug = true;
            $amount = -1;
        }

        $path = $this->configuration->getContext('workpath').'/invalid_items.csv';
        $this->invalidItems = $this->getLines($path);

        if ($pipeline = $this->configuration->getPipeline()) {
            $pipeline->setLogger($this->configuration->getLogger());
            if ($debug === true) {
                if ($mappings === true) {
                    dump($this->configuration->getMappings());
                }
                $pipeline
                    ->line(New LoggingPipe())
                    ->runInDebugMode($amount ?? 1, $line);
                exit;
            }

            if (is_int($amount)) {
                $pipeline->run($amount);
                $this->stopProcess();
                return;
            }

            $pipeline->run();
        }

        $this->stopProcess();
    }

    public function stopProcess(): void
    {
        $memoryUsageMB = round(memory_get_usage() / 1024 / 1024, 2);
        $peakMemoryUsageMB = round(memory_get_peak_usage() / 1024 / 1024);
        $usage = "Memory Usage: $memoryUsageMB/$peakMemoryUsageMB MB";

        $stopTimeStamp = microtime(true);
        $executionTime = round($stopTimeStamp - $this->startTimeStamp, 1);
        $executionTime = "Execution Time: {$executionTime}s";

        $path = $this->configuration->getContext('workpath').'/invalid_items.csv';
        $this->invalidItems = $this->getLines($path) - $this->invalidItems;
        $invalidItems = "Invalid Items: $this->invalidItems";

        $this->log(sprintf(
            "Finished Step :: %s (%s, %s, %s)",
            basename($this->configuration->getContext('transformation_file')),
            $usage,
            $executionTime,
            $invalidItems
        ));
    }

    private function getLines($file): int
    {
        $f = fopen($file, 'rb');
        $lines = 0;
        while (!feof($f)) {
            $lines += substr_count(fread($f, 8192), "\n");
        }
        fclose($f);
        return $lines;
    }
}