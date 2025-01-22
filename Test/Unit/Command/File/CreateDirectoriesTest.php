<?php

namespace MageSuite\Importer\Test\Unit\Command\File;

class CreateDirectoriesTest extends \PHPUnit\Framework\TestCase
{
    protected ?\MageSuite\Importer\Command\File\CreateDirectories $command = null;
    protected ?\Magento\Framework\Filesystem\Io\File $fileIo = null;
    protected ?\MageSuite\Importer\Model\Command\OutputFactory $outputFactory = null;
    protected string $assetsDirectory;
    protected string $assetsDirectoryRelativeToMainDirectory;

    protected array $directoriesPaths = [
        '/var/images',
        '/var/import'
    ];

    public function setUp(): void
    {
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->fileIo = $objectManager->get(\Magento\Framework\Filesystem\Io\File::class);
        $this->command = $objectManager->get(\MageSuite\Importer\Command\File\CreateDirectories::class);
        $this->outputFactory = $objectManager->get(\MageSuite\Importer\Model\Command\OutputFactory::class);
        $this->assetsDirectory = realpath(__DIR__.'/../assets');
        $this->assetsDirectoryRelativeToMainDirectory = str_replace(BP . '/', '', $this->assetsDirectory);
    }

    public function testItCreatesMultipleDirectoriesProperly(): void
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

    public function testItDoesNotCreateDirectoryWhenItDoesExist(): void
    {
        $fileIoMock = $this->getMockBuilder(\Magento\Framework\Filesystem\Io\File::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fileIoMock->expects($this->never())
            ->method('mkdir');

        $fileIoMock->method('fileExists')
            ->willReturn(true);

        $command = new \MageSuite\Importer\Command\File\CreateDirectories($fileIoMock, $this->outputFactory);
        $command->execute(['directories_paths' => [$this->assetsDirectoryRelativeToMainDirectory . '/existing_directory']]);
    }

    public function tearDown(): void
    {
        foreach ($this->directoriesPaths as $directoryPath) {
            if (is_dir($this->assetsDirectory . $directoryPath)) {
                $this->fileIo->rmdir($this->assetsDirectory . $directoryPath, true);
            }
        }

        if (is_dir($this->assetsDirectory . '/var')) {
            $this->fileIo->rmdir($this->assetsDirectory . '/var', true);
        }
    }
}
