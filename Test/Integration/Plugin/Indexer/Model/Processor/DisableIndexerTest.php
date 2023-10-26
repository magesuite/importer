<?php

namespace MageSuite\Importer\Test\Integration\Plugin\Indexer\Model\Processor;

class DisableIndexerTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\TestFramework\ObjectManager $objectManager = null;
    protected ?\MageSuite\Importer\Plugin\Indexer\Model\Processor\DisableIndexer $plugin = null;
    protected ?\PHPUnit\Framework\MockObject\MockObject $indexerProcessorDummy = null;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->plugin = $this->objectManager->get(\MageSuite\Importer\Plugin\Indexer\Model\Processor\DisableIndexer::class);
        $this->indexerProcessorDummy = $this->getMockBuilder(\Magento\Indexer\Model\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @magentoAdminConfigFixture indexer/indexing/enabled 1
     */
    public function testAroundUpdateMviewIndexerIsEnabled()
    {
        $wasCalled = false;

        $this->plugin->aroundUpdateMview($this->indexerProcessorDummy, function () use (&$wasCalled) {
            $wasCalled = true;
        });

        $this->assertTrue($wasCalled);
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoDataFixture disableIndexerFixture
     */
    public function testAroundUpdateMviewIndexerIsDisabled()
    {
        $this->expectException(\Exception::class);

        $this->plugin->aroundUpdateMview($this->indexerProcessorDummy, function () {
        });
    }

    /**
     * @magentoAdminConfigFixture indexer/indexing/enabled 1
     */
    public function testReindexAllInvalidIndexerIsEnabled()
    {
        $wasCalled = false;

        $this->plugin->aroundReindexAllInvalid($this->indexerProcessorDummy, function () use (&$wasCalled) {
            $wasCalled = true;
        });

        $this->assertTrue($wasCalled);
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoDataFixture disableIndexerFixture
     */
    public function testReindexAllInvalidIndexerIsDisabled()
    {
        $this->expectException(\Exception::class);

        $this->plugin->aroundReindexAllInvalid($this->indexerProcessorDummy, function () {
        });
    }

    public static function disableIndexerFixture()
    {
        $configWriter = self::getConfigWriter();
        $configWriter->save(
            \MageSuite\Importer\Helper\Config::INDEXER_ENABLED_XML_PATH,
            '0'
        );
    }

    public static function disableIndexerFixtureRollback()
    {
        $configWriter = self::getConfigWriter();
        $configWriter->save(
            \MageSuite\Importer\Helper\Config::INDEXER_ENABLED_XML_PATH,
            '1'
        );
    }

    protected static function getConfigWriter()
    {
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        return $objectManager->get(\Magento\Framework\App\Config\Storage\WriterInterface::class);
    }
}
