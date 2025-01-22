<?php

namespace MageSuite\Importer\Test\Unit\Command\File;

class DownloadNewestTest extends AbstractDownloader
{
    public function setUp(): void
    {
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->fileDownloaderDouble = $this
            ->getMockBuilder(\Creativestyle\LFTP\File\Downloader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager->addSharedInstance($this->fileDownloaderDouble, \Creativestyle\LFTP\File\Downloader::class);
        $this->command = $objectManager->create(\MageSuite\Importer\Command\File\DownloadNewest::class);
    }

    public function testItDownloadsNewestFile()
    {
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
