<?php

namespace MageSuite\Importer\Test\Unit\Command\File;

abstract class DownloaderTest extends \PHPUnit\Framework\TestCase
{
    protected $command;

    protected $fileDownloaderDouble;

    public function testItImplementsCommandInterface()
    {
        $this->assertInstanceOf(\MageSuite\Importer\Command\Command::class, $this->command);
    }

    public function testItProperlySetsServerConfiguration()
    {
        $expectations = [
            ['setProtocol', 'ftp'],
            ['setHost', 'domain.com'],
            ['setUsername', 'user'],
            ['setPassword', 'pass'],
        ];

        foreach ($expectations as $expectation) {
            list($expectedMethod, $expectedArgument) = $expectation;

            $this->fileDownloaderDouble
                ->expects($this->atLeastOnce())
                ->method($expectedMethod)
                ->with($expectedArgument);
        }

        $this->command->execute([
            'protocol' => 'ftp',
            'host' => 'domain.com',
            'username' => 'user',
            'password' => 'pass',
            'remote_path' => 'remote_path',
            'target_path' => 'target_path',
            'remote_directory' => '',
            'target_directory' => ''
        ]);
    }
}
