<?php

namespace MageSuite\Importer\Test\Integration\Services\Import;

class FailedImportDetectorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \MageSuite\Importer\Services\Import\FailedImportDetector
     */
    protected $detector;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTimeStub;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->dateTimeStub = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->detector = $this->objectManager->create(
            \MageSuite\Importer\Services\Import\FailedImportDetector::class,
            ['dateTime' => $this->dateTimeStub]
        );
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture loadInProgressImport
     * @dataProvider currentTimeWithExpectedStatus
     */
    public function testItMarksImportWithCorrectStatusDependingOnCurrentTime($currentDateTime, $expectedStatus)
    {
        $this->dateTimeStub
            ->method('timestamp')
            ->willReturn(strtotime($currentDateTime));

        $this->detector->markFailedImports();

        $import = $this->objectManager->create(\MageSuite\Importer\Model\Import::class);
        $import->load('in_progress', 'hash');

        $importStep = $this->objectManager->get(\MageSuite\Importer\Model\ImportStep::class);
        $importStep->load($import->getId(), 'import_id');

        $this->assertEquals($expectedStatus, $import->getStatus());
        $this->assertEquals($expectedStatus, $importStep->getStatus());
    }

    public static function loadInProgressImport()
    {
        require __DIR__ . '/../../_files/in_progress_import.php';
    }

    public function currentTimeWithExpectedStatus()
    {
        return [
            ['2018-07-19 12:30:00', \MageSuite\Importer\Model\ImportStep::STATUS_ERROR],
            ['2018-07-19 09:30:00', \MageSuite\Importer\Model\ImportStep::STATUS_IN_PROGRESS]
        ];
    }
}