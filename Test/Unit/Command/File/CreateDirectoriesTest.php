<?php

namespace MageSuite\Importer\Test\Unit\Command\File;

class CreateDirectoriesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \MageSuite\Importer\Command\File\CreateDirectories
     */
    protected $command;
    protected $assetsDirectory;
    protected $assetsDirectoryRelativeToMainDirectory;

    protected $directoriesPaths = [
        '/var/images',
        '/var/import'
    ];

    public function setUp(): void
    {
        $this->command = new \MageSuite\Importer\Command\File\CreateDirectories();

        $this->assetsDirectory = realpath(__DIR__.'/../assets');
        $this->assetsDirectoryRelativeToMainDirectory = str_replace(BP . '/', '', $this->assetsDirectory);
    }

    public function testItCreatesMultipleDirectoriesProperly()
    {
        $directories = [];

        foreach ($this->directoriesPaths as $directoryPath) {
            $directories[] = $this->assetsDirectoryRelativeToMainDirectory . $directoryPath;
        }

        foreach ($this->directoriesPaths as $directoryPath) {
            $this->assertFalse(is_dir($this->assetsDirectory . $directoryPath));
        }

        $this->command->execute(['directories_paths' => $directories]);

        foreach ($this->directoriesPaths as $directoryPath) {
            $this->assertTrue(is_dir($this->assetsDirectory . $directoryPath));
        }
    }

    public function testItDoesNotCreateDirectoryWhenItDoesExist()
    {
        $this->command->execute(['directories_paths' => [$this->assetsDirectoryRelativeToMainDirectory . '/existing_directory']]);
    }

    public function tearDown(): void
    {
        foreach ($this->directoriesPaths as $directoryPath) {
            if (is_dir($this->assetsDirectory . $directoryPath)) {
                rmdir($this->assetsDirectory . $directoryPath);
            }
        }

        if (is_dir($this->assetsDirectory . '/var')) {
            rmdir($this->assetsDirectory . '/var');
        }
    }
}
