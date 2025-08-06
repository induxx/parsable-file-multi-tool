<?php

namespace Misery\Component\Shell;

use Misery\Component\Common\Utils\ValueFormatter;

class ShellCommands
{
    /** @var string[] */
    private array $commands = [];
    private array $context;

    public function __construct(array $context)
    {
        $this->context = $context;
    }

    public function addCommand(string $name, string $command): void
    {
        $this->commands[$name] = ValueFormatter::format($command, $this->context);
    }

    public function addScript(
        string $name,
        string $script,
        array $args = [],
        string $executable = '/usr/bin/bash'
    ): void {
        $script = ValueFormatter::format($script, $this->context);
        $args   = array_map(fn($a) => ValueFormatter::format($a, $this->context), $args);
        $this->commands[$name] = implode(' ', array_merge(
            [$executable, escapeshellarg($script)],
            array_map('escapeshellarg', $args)
        ));
    }

    /**
     * @throws Exception\ShellExecutionException
     */
    public function exec(): void
    {
        foreach ($this->commands as $name => $cmd) {
            // Wrap in bash -lc so that brace expansion, variables, etc. work
            $fullCommand = sprintf('bash -lc %s', escapeshellarg($cmd));

            $descriptors = [
                1 => ['pipe', 'w'], // stdout
                2 => ['pipe', 'w'], // stderr
            ];

            $process = proc_open($fullCommand, $descriptors, $pipes);
            if (!is_resource($process)) {
                throw new Exception\ShellExecutionException("Unable to start process for command “{$name}”");
            }

            $stdout = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[2]);

            $exitCode = proc_close($process);
            if ($exitCode !== 0) {
                throw new Exception\ShellExecutionException(
                    sprintf(
                        'Command “%s” failed (exit %d). Stderr: %s',
                        $name,
                        $exitCode,
                        trim($stderr) ?: '(no stderr output)'
                    )
                );
            }

            // Optionally, you can log or verify $stdout here if you expect specific output
        }
    }
}
