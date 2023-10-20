<?php

declare(strict_types=1);

namespace MageSuite\Importer\Test\Integration\Plugin\ServerStatusLogger\Model\StatusResolver;

class DisableMviewStateTest extends \PHPUnit\Framework\TestCase
{
    public function setUp():void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->mviewCollectionStub = $this->getMockBuilder(\Magento\Framework\Mview\View\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager->removeSharedInstance(\MageSuite\ServerStatusLogger\Model\StatusResolver\MviewState::class, true);

        $this->objectManager->addSharedInstance(
            $this->mviewCollectionStub,
            \Magento\Framework\Mview\View\Collection::class,
            true
        );

        $this->generateCurrentStatus = $this->objectManager->create(\MageSuite\ServerStatusLogger\Model\GenerateCurrentStatus::class);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture disableIndexerFixture
     */
    public function testIfIndexersAreDisabled(): void
    {
        $this->mviewCollectionStub->expects($this->never())->method('getItems');

        $this->generateCurrentStatus->execute();
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testIfIndexersAreEnabled(): void
    {
        $this->mviewCollectionStub->expects($this->once())->method('getItems')->willReturn([]);

        $this->generateCurrentStatus->execute();
    }

    public static function disableIndexerFixture(): void
    {
        $configWriter = self::getConfigWriter();
        $configWriter->save(\MageSuite\Importer\Helper\Config::INDEXER_ENABLED_XML_PATH, '0');
    }

    public static function disableIndexerFixtureRollback(): void
    {
        $configWriter = self::getConfigWriter();
        $configWriter->save(\MageSuite\Importer\Helper\Config::INDEXER_ENABLED_XML_PATH, '1');
    }

    protected static function getConfigWriter(): \Magento\Framework\App\Config\Storage\WriterInterface
    {
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        return $objectManager->get(\Magento\Framework\App\Config\Storage\WriterInterface::class);
    }
}
