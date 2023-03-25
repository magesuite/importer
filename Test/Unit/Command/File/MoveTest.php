<?php

namespace MageSuite\Importer\Test\Unit\Command\File;

class MoveTest extends \PHPUnit\Framework\TestCase
{
    protected ?\MageSuite\Importer\Command\File\Move $command = null;
    protected ?\Magento\Framework\Filesystem\Io\File $fileIo = null;
    protected $assetsDirectory;
    protected $assetsDirectoryRelativeToMainDirectory;

    public function setUp(): void
    {
        $this->assetsDirectory = realpath(__DIR__ . '/../assets');
        $this->assetsDirectoryRelativeToMainDirectory = str_replace(BP . '/', '', $this->assetsDirectory);
        $this->command = new \MageSuite\Importer\Command\File\Move();
        $this->fileIo = new \Magento\Framework\Filesystem\Io\File();
    }

    public function testItThrowsExceptionWhenSourcePathIsNotDefined()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->command->execute(['target_path' => 'something']);
    }

    public function testItThrowsExceptionWhenTargetPathIsNotDefined()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->command->execute(['source_path' => 'something']);
    }

    public function testItThrowsExceptionWhenSourceFileDoesNotExists()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->command->execute([
            'source_path' => $this->assetsDirectoryRelativeToMainDirectory . '/not_existing_file',
            'target_path' => $this->assetsDirectoryRelativeToMainDirectory . '/target_path'
        ]);
    }

    public function testItMovesFile()
    {
        $this->fileIo->cp($this->assetsDirectory.'/existing_file', $this->assetsDirectory.'/file_to_be_moved');

        $this->command->execute([
            'source_path' => $this->assetsDirectoryRelativeToMainDirectory . '/file_to_be_moved',
            'target_path' => $this->assetsDirectoryRelativeToMainDirectory . '/target_path'
        ]);

        $this->assertEquals(
            'existing_file_contents',
            $this->fileIo->read($this->assetsDirectory.'/target_path')
        );
        $this->assertFalse(
            $this->fileIo->fileExists($this->assetsDirectoryRelativeToMainDirectory . '/file_to_be_moved')
        );
    }

    public function tearDown(): void
    {
        if ($this->fileIo->fileExists($this->assetsDirectory.'/target_path')) {
            $this->fileIo->rm($this->assetsDirectory.'/target_path');
        }
    }
}
