<?php

namespace MageSuite\Importer\Test\Unit\Command\File;

class SyncTest extends AbstractDownloader
{
    public function setUp(): void
    {
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->fileDownloaderDouble = $this
            ->getMockBuilder(\Creativestyle\LFTP\File\Downloader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager->addSharedInstance($this->fileDownloaderDouble, \Creativestyle\LFTP\File\Downloader::class);
        $this->command = $objectManager->create(\MageSuite\Importer\Command\File\Sync::class);
    }

    public function testItSyncsFolders()
    {
        $this->fileDownloaderDouble
            ->expects($this->atLeastOnce())
            ->method('sync')
            ->with('remote_directory', 'target_directory');

        $this->command->execute([
            'remote_directory' => 'remote_directory',
            'target_directory' => 'target_directory'
        ]);
    }
}
