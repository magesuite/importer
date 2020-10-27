<?php

namespace MageSuite\Importer\Test\Unit\Command\File;

class DownloadNewestTest extends DownloaderTest
{
    public function setUp(): void
    {
        $this->fileDownloaderDouble = $this
            ->getMockBuilder(\Creativestyle\LFTP\File\Downloader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->command = new \MageSuite\Importer\Command\File\DownloadNewest($this->fileDownloaderDouble);
    }

    public function testItDownloadsNewestFile() {
        $this->fileDownloaderDouble
            ->expects($this->atLeastOnce())
            ->method('downloadNewest')
            ->with('remote_directory', 'target_path');

        $this->command->execute([
            'remote_directory' => 'remote_directory',
            'target_path' => 'target_path'
        ]);
    }
}
