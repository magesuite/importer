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

    /**
     * Removes one to many relations if related entities will be updated during import
     * @param $products
     * @param $skuProcessor
     */
    public function deleteRelations($products, $skuProcessor)
    {
        $productIdsToDeleteCategories = [];
        $productIdsToDeleteRelatedProducts = [];
        $productIdsToDeleteCrosssellProducts = [];
        $productIdsToDeleteUpSellProducts = [];
        $productIdsToDeleteConfigurableVariations = [];

        $linkField = $this->getProductEntityLinkField();

        $imagesChanges = [];

        foreach ($products as $product) {
            if (empty($skuProcessor->getNewSku($product['sku']))) {
                continue;
            }

            $productId = $skuProcessor->getNewSku($product['sku'])[$linkField];
            $entityId = $skuProcessor->getNewSku($product['sku'])['entity_id'];

            if (empty($productId)) {
                continue;
            }

            if (array_key_exists('categories', $product)) {
                $productIdsToDeleteCategories[] = $entityId;
            }

            if (array_key_exists('related_skus', $product)) {
                $productIdsToDeleteRelatedProducts[] = $productId;
            }

            if (array_key_exists('crosssell_skus', $product)) {
                $productIdsToDeleteCrosssellProducts[] = $productId;
            }

            if (array_key_exists('upsell_skus', $product)) {
                $productIdsToDeleteUpSellProducts[] = $productId;
            }

            if (array_key_exists('configurable_variations', $product)) {
                $productIdsToDeleteConfigurableVariations[] = $productId;
            }

            if ($this->productHasImagesChanges($product)) {
                $imagesChanges[$productId] = $this->getProductImagesChanges($product);
            }
        }

        $this->imagesManager->deleteImages($imagesChanges);

        $this->deleteCategoriesRelations($productIdsToDeleteCategories);

        $this->deleteLinks(
            $productIdsToDeleteRelatedProducts,
            \Magento\Catalog\Model\Product\Link::LINK_TYPE_RELATED
        );
        $this->deleteLinks(
            $productIdsToDeleteCrosssellProducts,
            \Magento\Catalog\Model\Product\Link::LINK_TYPE_CROSSSELL
        );
        $this->deleteLinks(
            $productIdsToDeleteUpSellProducts,
            \Magento\Catalog\Model\Product\Link::LINK_TYPE_UPSELL
        );

        $this->deleteConfigurableProductRelations($productIdsToDeleteConfigurableVariations);
    }

    /**
     * @param $connection
     * @param $productsIds
     */
    protected function deleteLinks($productsIds, $linkType)
    {
        if (empty($productsIds)) {
            return;
        }

        $productLinksTable = $this->resourceConnection->getTableName('catalog_product_link');

        $this->connection->delete(
            $productLinksTable,
            ['product_id IN (?)' => $productsIds, 'link_type_id' => $linkType]
        );
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

    /**
     * @param $productIds
     */
    protected function deleteConfigurableProductRelations($productIds)
    {
        if (empty($productIds)) {
            return;
        }

        $this->connection->delete(
            $this->resourceConnection->getTableName('catalog_product_relation'),
            $this->connection->quoteInto('parent_id IN (?)', $productIds)
        );

        $this->connection->delete(
            $this->resourceConnection->getTableName('catalog_product_super_link'),
            $this->connection->quoteInto('parent_id IN (?)', $productIds)
        );
    }

    /**
     * @param $productIds
     */
    protected function deleteCategoriesRelations($productIds)
    {
        if (empty($productIds)) {
            return;
        }

        $categoriesTableName = $this->productResourceModel->getProductCategoryTable();

        $this->connection->delete(
            $categoriesTableName,
            $this->connection->quoteInto('product_id IN (?)', $productIds)
        );
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
