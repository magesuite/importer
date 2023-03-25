<?php

namespace MageSuite\Importer\Test\Integration\Repository;

class ImportRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \MageSuite\Importer\Api\ImportRepositoryInterface
     */
    protected $repository;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->repository = $this->objectManager->get(\MageSuite\Importer\Api\ImportRepositoryInterface::class);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture loadActiveImport
     */
    public function testItReturnsActiveImportWhenItExists()
    {
        $activeImport = $this->repository->getActiveImport();

        $this->assertNotNull($activeImport->getId());
        $this->assertEquals('active_hash', $activeImport->getHash());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture loadFinishedImports
     */
    public function testItDoesNotReturnActiveImportWhenAllImportsAreDoneOrHaveError()
    {
        $activeImport = $this->repository->getActiveImport();

        $this->assertNull($activeImport->getId());
    }

    public static function loadActiveImport()
    {
        require __DIR__.'/../_files/active_import.php';
    }

    public static function loadFinishedImports()
    {
        require __DIR__.'/../_files/finished_import.php';
    }
}
