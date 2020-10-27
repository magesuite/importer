<?php

namespace MageSuite\Importer\Test\Unit\Services\File;

class WriterTest extends \PHPUnit\Framework\TestCase
{
    protected $filePath;

    /**
     * @var \MageSuite\Importer\Services\File\Writer
     */
    protected $writer;

    public function setUp(): void
    {
        $this->filePath = __DIR__ . '/../assets/write_test';

        $this->writer = new \MageSuite\Importer\Services\File\Writer($this->filePath);
    }

    public function testItWritesToFileProperly()
    {
        $this->writer->writeLine('first_line');
        $this->writer->writeLine('second_line');

        $this->assertEquals(["first_line".PHP_EOL, 'second_line'], file($this->filePath));
    }

    public function tearDown(): void
    {
        if(!file_exists($this->filePath)) {
            return;
        }

        unlink($this->filePath);
    }

}
