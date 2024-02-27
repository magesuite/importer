<?php

namespace MageSuite\Importer\Services\Import;

class ProductRelationsManager
{
    /**
     * @var
     */
    protected \Magento\Framework\EntityManager\MetadataPool $metadataPool;

    /**
     * @var
     */
    protected \Magento\Framework\App\ResourceConnection $resourceConnection;

    /**
     * @var
     */
    protected \Magento\Framework\DB\Adapter\AdapterInterface $connection;

    /**
     * @var
     */
    protected \Magento\Catalog\Model\ResourceModel\Product $productResourceModel;

    /**
     * @var
     */
    protected \Magento\Catalog\Model\ResourceModel\Product\Link $productLinkResourceModel;

    /**
     * @var
     */
    protected \MageSuite\Importer\Services\Import\MediaGalleryImagesManager $imagesManager;

    protected $productEntityLinkField;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product $productResourceModel,
        \Magento\Catalog\Model\ResourceModel\Product\Link $productLinkResourceModel,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \MageSuite\Importer\Services\Import\MediaGalleryImagesManager $imagesManager,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool
    ) {
        $this->productResourceModel = $productResourceModel;
        $this->productLinkResourceModel = $productLinkResourceModel;
        $this->resourceConnection = $resourceConnection;
        $this->connection = $resourceConnection->getConnection();
        $this->imagesManager = $imagesManager;
        $this->metadataPool = $metadataPool;
    }

    public function deleteRelations($products, $skuProcessor)
    {
        $linkField = $this->getProductEntityLinkField();

        $imagesChanges = [];

        foreach ($products as $product) {
            if (empty($skuProcessor->getNewSku($product['sku']))) {
                continue;
            }

            $productId = $skuProcessor->getNewSku($product['sku'])[$linkField];

            if (empty($productId)) {
                continue;
            }

            if ($this->productHasImagesChanges($product)) {
                $imagesChanges[$productId] = $this->getProductImagesChanges($product);
            }
        }

        $this->imagesManager->deleteImages($imagesChanges);
    }

    protected function productHasImagesChanges($product)
    {
        foreach ($this->imagesManager->getImportArrayKeysContainingImagesChanges() as $key) {
            if (array_key_exists($key, $product)) {
                return true;
            }
        }

        return false;
    }

    public function getProductImagesChanges(array $product): array
    {
        $imagesChanges = [];

        foreach ($this->imagesManager->getImportArrayKeysContainingImagesChanges() as $key) {
            if (array_key_exists($key, $product)) {
                $imagesChanges[$key] = $product[$key] ?? '';
            }
        }

        return $imagesChanges;
    }

    protected function getProductEntityLinkField()
    {
        if (!$this->productEntityLinkField) {
            $this->productEntityLinkField = $this->metadataPool
                ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
                ->getLinkField();
        }
        return $this->productEntityLinkField;
    }
}
