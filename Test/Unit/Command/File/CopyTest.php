<?php

namespace MageSuite\Importer\Test\Unit\Command\File;

class CopyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \MageSuite\Importer\Command\File\CreateDirectories
     */
    protected $command;
    protected $assetsDirectory;
    protected $assetsDirectoryRelativeToMainDirectory;

    public function setUp()
    {
        $this->assetsDirectory = realpath(__DIR__ . '/../assets');
        $this->assetsDirectoryRelativeToMainDirectory = str_replace(BP . '/', '', $this->assetsDirectory);

        $this->command = new \MageSuite\Importer\Command\File\Copy();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testItThrowsExceptionWhenSourcePathIsNotDefined()
    {
        $this->command->execute(['target_path' => 'something']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testItThrowsExceptionWhenTargetPathIsNotDefined()
    {
        $this->command->execute(['source_path' => 'something']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testItThrowsExceptionWhenSourceFileDoesNotExists()
    {
        $this->command->execute([
            'source_path' => $this->assetsDirectoryRelativeToMainDirectory . '/not_existing_file',
            'target_path' => $this->assetsDirectoryRelativeToMainDirectory . '/target_path'
        ]);
    }

    public function testItCopiesFile()
    {
        $this->assertFalse(file_exists($this->assetsDirectory.'/target_path'));

        $this->command->execute([
            'source_path' => $this->assetsDirectoryRelativeToMainDirectory . '/existing_file',
            'target_path' => $this->assetsDirectoryRelativeToMainDirectory . '/target_path'
        ]);

        $this->assertEquals('existing_file_contents', file_get_contents($this->assetsDirectory.'/target_path'));
    }

    public function tearDown()
    {
        if(file_exists($this->assetsDirectory.'/target_path')) {
            unlink($this->assetsDirectory.'/target_path');
        }
    }
}