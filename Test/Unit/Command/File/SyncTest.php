<?php

namespace MageSuite\Importer\Test\Unit\Command\File;

class SyncTest extends DownloaderTest
{
    public function setUp(): void
    {
        $this->fileDownloaderDouble = $this
            ->getMockBuilder(\Creativestyle\LFTP\File\Downloader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->command = new \MageSuite\Importer\Command\File\Sync($this->fileDownloaderDouble);
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
