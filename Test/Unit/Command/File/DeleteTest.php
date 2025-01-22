<?php

namespace MageSuite\Importer\Test\Unit\Command\File;

class DeleteTest extends \PHPUnit\Framework\TestCase
{
    protected ?\MageSuite\Importer\Command\File\Delete $command = null;
    protected ?\Magento\Framework\Filesystem\Io\File $fileIo = null;
    protected ?string $assetsDirectory = null;
    protected ?string $assetsDirectoryRelativeToMainDirectory = null;

    public function setUp(): void
    {
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->fileIo = $objectManager->get(\Magento\Framework\Filesystem\Io\File::class);
        $this->command = $objectManager->get(\MageSuite\Importer\Command\File\Delete::class);
        $this->assetsDirectory = realpath(__DIR__ . '/../assets');
        $this->assetsDirectoryRelativeToMainDirectory = str_replace(BP . '/', '', $this->assetsDirectory);
    }

    public function testItThrowsExceptionWhenPathIsNotDefined()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->command->execute(['key' => 'something']);
    }

    public function testItDeletesFile()
    {
        $this->fileIo->cp(
            $this->assetsDirectory.'/existing_file',
            $this->assetsDirectory.'/file_to_be_deleted'
        );

        $this->assertTrue($this->fileIo->fileExists($this->assetsDirectory.'/file_to_be_deleted'));

        $this->command->execute([
            'path' => $this->assetsDirectoryRelativeToMainDirectory . '/file_to_be_deleted'
        ]);

        $this->assertFalse($this->fileIo->fileExists($this->assetsDirectory.'/file_to_be_deleted'));
    }

    public function tearDown(): void
    {
        if ($this->fileIo->fileExists($this->assetsDirectory.'/file_to_be_deleted')) {
            $this->fileIo->rm($this->assetsDirectory.'/file_to_be_deleted');
        }
    }
}
