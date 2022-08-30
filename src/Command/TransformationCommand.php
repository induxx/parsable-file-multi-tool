<?php

namespace Misery\Command;

use Ahc\Cli\Input\Command;
use Assert\Assertion;
use Misery\Component\Common\FileManager\LocalFileManager;
use Misery\Component\Process\ProcessManager;
use Symfony\Component\Yaml\Yaml;

/**
 * @usage
 * bin/console transformation --file /path/to/transformation_file --sources /path/to/sources/dir
 */
class TransformationCommand extends Command
{
    private $file;
    private $sources;
    private $workpath;
    private $debug;
    private $showMappings;
    private $try;

    public function __construct()
    {
        parent::__construct('transformation', 'run a transform command based on a transformation file');

        $this
            ->option('-f --file', 'The transformation file location')
            ->option('-s --source', 'The sources location')
            ->option('-w --workpath', 'The workpath location')
            ->option('-d --debug', 'enable debugging', 'boolval', false)
            ->option('-m --showMappings', 'show lists or mappings', 'boolval', false)
            ->option('-t --try', 'tryout a set for larger files')

            ->usage(
                '<bold>  transformation</end> <comment>--file /path/to/transformation_file --source /path/to/sources/dir</end> ## detailed<eol/>'.
                '<bold>  transformation</end> <comment>-f /path/to/transformation -s /path/to/sources/dir</end> ## short<eol/>'
            )
        ;
    }

    public function execute(string $file, string $source, string $workpath, bool $debug, int $try = null, bool $showMappings = null)
    {
        $io = $this->app()->io();

        Assertion::file($file);
        Assertion::directory($source);
        Assertion::directory($workpath);

        $transformationsPath = dirname($file);

        require_once __DIR__.'/../../src/bootstrap.php';

        $fm = new LocalFileManager($source);

        $configurationFactory = initConfigurationFactory();
        $configurationFactory->init($fm);

        $configuration = $configurationFactory->parseDirectivesFromConfiguration(
            array_merge(Yaml::parseFile($file), [
                'context' => [
                    'transformation_file' => $file,
                    'sources' => $source,
                    'workpath' => $workpath,
                    'transformations' => $transformationsPath,
                    'scripts' => '/app/scripts',
                    'debug' => $debug,
                    'try' => $try,
                    'show_mappings' => $showMappings,
                ]
            ])
        );

        (new ProcessManager($configuration))->startProcess();

        // TODO connect the outputs here
        if ($shellCommands = $configuration->getShellCommands()) {
            $shellCommands->exec();
            $configuration->clearShellCommands();
        }
    }
}