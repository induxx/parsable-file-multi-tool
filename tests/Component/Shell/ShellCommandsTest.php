<?php

declare(strict_types=1);

namespace Tests\Misery\Component\Shell;

use Misery\Component\Shell\ShellCommands;
use Misery\Component\Shell\Exception\ShellExecutionException;
use PHPUnit\Framework\TestCase;

class ShellCommandsTest extends TestCase
{
    public function test_addCommand_and_exec_success(): void
    {
        $context = [];
        $shell = new ShellCommands($context);
        $shell->addCommand('echo', 'echo "Hello World"');
        $this->expectNotToPerformAssertions();
        $shell->exec();
    }

    public function test_addScript_and_exec_success(): void
    {
        $context = [];
        $shell = new ShellCommands($context);
        $tmpFile = tempnam(sys_get_temp_dir(), 'test_script_');
        file_put_contents($tmpFile, '#!/bin/bash\necho "Script OK"\n');
        chmod($tmpFile, 0700);
        $shell->addScript('test_script', $tmpFile);
        $this->expectNotToPerformAssertions();
        $shell->exec();
        unlink($tmpFile);
    }
}

