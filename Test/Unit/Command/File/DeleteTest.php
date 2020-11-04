<?php

namespace MageSuite\Importer\Test\Unit\Command\File;

class DeleteTest extends \PHPUnit\Framework\TestCase
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

        $this->command = new \MageSuite\Importer\Command\File\Delete();
    }

    public function testItThrowsExceptionWhenPathIsNotDefined()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->command->execute(['key' => 'something']);
    }

    public function testItDeletesFile()
    {
        copy($this->assetsDirectory.'/existing_file', $this->assetsDirectory.'/file_to_be_deleted');

        $this->assertTrue(file_exists($this->assetsDirectory.'/file_to_be_deleted'));

        $this->command->execute([
            'path' => $this->assetsDirectoryRelativeToMainDirectory . '/file_to_be_deleted'
        ]);

        $this->assertFalse(file_exists($this->assetsDirectory.'/file_to_be_deleted'));
    }

    public function tearDown(): void
    {
        if(file_exists($this->assetsDirectory.'/file_to_be_deleted')) {
            unlink($this->assetsDirectory.'/file_to_be_deleted');
        }
    }
}
