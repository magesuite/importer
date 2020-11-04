<?php

namespace MageSuite\Importer\Test\Unit\Command\File;

class MoveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \MageSuite\Importer\Command\File\CreateDirectories
     */
    protected $command;
    protected $assetsDirectory;
    protected $assetsDirectoryRelativeToMainDirectory;

    public function setUp(): void
    {
        $this->assetsDirectory = realpath(__DIR__ . '/../assets');
        $this->assetsDirectoryRelativeToMainDirectory = str_replace(BP . '/', '', $this->assetsDirectory);

        $this->command = new \MageSuite\Importer\Command\File\Move();
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
        copy($this->assetsDirectory.'/existing_file', $this->assetsDirectory.'/file_to_be_moved');

        $this->command->execute([
            'source_path' => $this->assetsDirectoryRelativeToMainDirectory . '/file_to_be_moved',
            'target_path' => $this->assetsDirectoryRelativeToMainDirectory . '/target_path'
        ]);

        $this->assertEquals('existing_file_contents', file_get_contents($this->assetsDirectory.'/target_path'));
        $this->assertFalse(file_exists($this->assetsDirectoryRelativeToMainDirectory . '/file_to_be_moved'));
    }

    public function tearDown(): void
    {
        if(file_exists($this->assetsDirectory.'/target_path')) {
            unlink($this->assetsDirectory.'/target_path');
        }
    }
}
