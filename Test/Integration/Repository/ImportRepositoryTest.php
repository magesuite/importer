<?php

namespace MageSuite\Importer\Test\Integration\Repository;

class ImportRepositoryTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\TestFramework\ObjectManager $objectManager = null;
    protected ?\MageSuite\Importer\Api\ImportRepositoryInterface $repository = null;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->repository = $this->objectManager->get(\MageSuite\Importer\Api\ImportRepositoryInterface::class);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture MageSuite_Importer::Test/Integration/_files/active_import.php
     */
    public function testItReturnsActiveImportWhenItExists()
    {
        $activeImport = $this->repository->getActiveImport();
        $this->assertNotNull($activeImport->getId());
        $this->assertEquals('active_hash', $activeImport->getHash());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture MageSuite_Importer::Test/Integration/_files/finished_import.php
     */
    public function testItDoesNotReturnActiveImportWhenAllImportsAreDoneOrHaveError()
    {
        $activeImport = $this->repository->getActiveImport();
        $this->assertNull($activeImport->getId());
    }
}
