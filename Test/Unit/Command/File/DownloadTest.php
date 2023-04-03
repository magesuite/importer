<?php

namespace MageSuite\Importer\Test\Unit\Command\File;

class DownloadTest extends AbstractDownloader
{
    public function setUp(): void
    {
        $this->fileDownloaderDouble = $this
            ->getMockBuilder(\Creativestyle\LFTP\File\Downloader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->command = new \MageSuite\Importer\Command\File\Download($this->fileDownloaderDouble);
    }

    public function testItDownloadsFile()
    {
        $this->fileDownloaderDouble
            ->expects($this->atLeastOnce())
            ->method('download')
            ->with('remote_path', 'target_path');

        $this->command->execute([
            'remote_path' => 'remote_path',
            'target_path' => 'target_path'
        ]);
    }
}
