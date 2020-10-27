<?php

namespace MageSuite\Importer\Test\Unit\Cron;

class SchedulerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \MageSuite\Importer\Services\Import\Scheduler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $schedulerMock;

    /**
     * @var \MageSuite\Importer\Cron\Scheduler
     */
    protected $cronScheduler;

    public function setUp(): void
    {
        $this->schedulerMock = $this->getMockBuilder(\MageSuite\Importer\Services\Import\Scheduler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cronScheduler = new \MageSuite\Importer\Cron\Scheduler($this->schedulerMock);
    }

    /**
     * @dataProvider getImportIdentifiers
     */
    public function testItSchedulesImportWithProperIdentifier($methodName, $importIdentifier) {
        $this->schedulerMock->expects($this->once())
            ->method('scheduleImport')
            ->with($importIdentifier);

        $this->cronScheduler->{$methodName}();
    }

    public static function getImportIdentifiers() {
        return [
            ['scheduleProductsImport', 'products_import'],
            ['scheduleStock', 'stock'],
        ];
    }
}
