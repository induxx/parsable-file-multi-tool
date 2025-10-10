<?php

declare(strict_types=1);

namespace Tests\Misery\Component\Common\FileManager;

use Misery\Component\Common\FileManager\LocalFileManager;
use PHPUnit\Framework\TestCase;

class LocalFileManagerTest extends TestCase
{
    private string $tmpDir;
    private LocalFileManager $manager;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/local_file_manager_test_' . uniqid();
        mkdir($this->tmpDir, 0777, true);
        $this->manager = new LocalFileManager($this->tmpDir);
    }

    protected function tearDown(): void
    {
        $this->deleteDir($this->tmpDir);
    }

    private function deleteDir($dir): void
    {
        if (!is_dir($dir)) return;
        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->deleteDir($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }

    public function test_add_and_get_file(): void
    {
        $this->manager->addFile('foo.txt', 'bar');
        $this->assertTrue($this->manager->isFile('foo.txt'));
        $this->assertSame('bar', $this->manager->getFileContent('foo.txt'));
    }

    public function test_copy_and_move_file(): void
    {
        $this->manager->addFile('a.txt', 'A');
        $this->manager->copyFile('a.txt', 'b.txt');
        $this->assertTrue($this->manager->isFile('b.txt'));
        $this->assertSame('A', $this->manager->getFileContent('b.txt'));
        $this->manager->moveFile('b.txt', 'c.txt');
        $this->assertFalse($this->manager->isFile('b.txt'));
        $this->assertTrue($this->manager->isFile('c.txt'));
        $this->assertSame('A', $this->manager->getFileContent('c.txt'));
    }

    public function test_remove_file_and_files(): void
    {
        $this->manager->addFile('x.txt', 'X');
        $this->manager->addFile('y.txt', 'Y');
        $this->manager->removeFile('x.txt');
        $this->assertFalse($this->manager->isFile('x.txt'));
        $this->manager->removeFiles('y.txt');
        $this->assertFalse($this->manager->isFile('y.txt'));
    }

    public function test_list_files_and_recursive(): void
    {
        $this->manager->addFile('root.txt', 'root');
        mkdir($this->tmpDir . '/subdir');
        $this->manager->addFile('subdir/child.txt', 'child');
        $files = iterator_to_array($this->manager->listFiles());
        $this->assertCount(2, $files);
        $this->assertStringContainsString('root.txt', $files[0] . $files[1]);
        $this->assertStringContainsString('child.txt', $files[0] . $files[1]);
    }

    public function test_create_sub_and_working_directory(): void
    {
        $sub = $this->manager->createSub('foo');
        $this->assertDirectoryExists($sub->getWorkingDirectory());
        $sub->addFile('bar.txt', 'baz');
        $this->assertSame('baz', $sub->getFileContent('bar.txt'));
    }

    public function test_clear(): void
    {
        $this->manager->addFile('clear1.txt', '1');
        $this->manager->addFile('clear2.txt', '2');
        $this->manager->clear();
        $files = iterator_to_array($this->manager->listFiles());
        $this->assertEmpty($files);
    }
}

