<?php

namespace MageSuite\Importer\Test\Unit\Services\File;

class WriterTest extends \PHPUnit\Framework\TestCase
{
    protected $filePath;
    protected ?\MageSuite\Importer\Services\File\Writer $writer = null;
    protected ?\Magento\Framework\Filesystem\Io\File $fileIo = null;

    public function setUp(): void
    {
        $this->filePath = __DIR__ . '/../assets/write_test';
        $this->writer = new \MageSuite\Importer\Services\File\Writer($this->filePath);
        $this->fileIo = new \Magento\Framework\Filesystem\Io\File();
    }

    public function testItWritesToFileProperly()
    {
        $this->writer->writeLine('first_line');
        $this->writer->writeLine('second_line');

        $this->assertEquals(["first_line".PHP_EOL, 'second_line'], file($this->filePath));
    }

    public function tearDown(): void
    {
        if (!$this->fileIo->fileExists($this->filePath)) {
            return;
        }

        $this->fileIo->rm($this->filePath);
    }
}
