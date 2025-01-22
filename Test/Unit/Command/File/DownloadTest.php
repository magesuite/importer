<?php

namespace MageSuite\Importer\Test\Unit\Command\File;

class DownloadTest extends AbstractDownloader
{
    public function setUp(): void
    {
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->fileDownloaderDouble = $this
            ->getMockBuilder(\Creativestyle\LFTP\File\Downloader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager->addSharedInstance($this->fileDownloaderDouble, \Creativestyle\LFTP\File\Downloader::class);
        $this->command = $objectManager->create(\MageSuite\Importer\Command\File\Download::class);
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
