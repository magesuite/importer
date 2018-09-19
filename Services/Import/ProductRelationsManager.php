<?php

namespace MageSuite\Importer\Services\Import;

class ProductRelationsManager
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    private $productResourceModel;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Link
     */
    private $productLinkResourceModel;

    /**
     * @var \MageSuite\Importer\Services\Import\MediaGalleryImagesManager
     */
    private $imagesManager;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product $productResourceModel,
        \Magento\Catalog\Model\ResourceModel\Product\Link $productLinkResourceModel,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \MageSuite\Importer\Services\Import\MediaGalleryImagesManager $imagesManager
    ) {
        $this->productResourceModel = $productResourceModel;
        $this->productLinkResourceModel = $productLinkResourceModel;

        $this->connection = $resourceConnection->getConnection();
        $this->imagesManager = $imagesManager;
    }

    /**
     * Removes one to many relations if related entities will be updated during import
     * @param $products
     * @param $skuProcessor
     */
    public function deleteRelations($products, $skuProcessor) {
        $productIdsToDeleteCategories = [];
        $productIdsToDeleteRelatedProducts = [];
        $productIdsToDeleteCrosssellProducts = [];
        $productIdsToDeleteUpSellProducts = [];
        $productIdsToDeleteConfigurableVariations = [];

        $imagesChanges = [];

        foreach ($products as $product) {
            $productId = $skuProcessor->getNewSku($product['sku'])['entity_id'];

            if (empty($productId)) {
                continue;
            }

            if (array_key_exists('categories', $product)) {
                $productIdsToDeleteCategories[] = $productId;
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

            if(array_key_exists('configurable_variations', $product)) {
                $productIdsToDeleteConfigurableVariations[] = $productId;
            }

            if ($this->productHasImagesChanges($product)) {
                $imagesChanges[$productId] = [];

                foreach ($this->imagesManager->getImportArrayKeysContainingImagesChanges() as $key) {
                    if (array_key_exists($key, $product)) {
                        $imagesChanges[$productId][$key] = ($product[$key] === null) ? '' : $product[$key];
                    }
                }
            }
        }

        $this->imagesManager->deleteImages($imagesChanges);

        $this->deleteCategoriesRelations($productIdsToDeleteCategories);

        $this->deleteLinks($productIdsToDeleteRelatedProducts,
            \Magento\Catalog\Model\Product\Link::LINK_TYPE_RELATED);
        $this->deleteLinks($productIdsToDeleteCrosssellProducts,
            \Magento\Catalog\Model\Product\Link::LINK_TYPE_CROSSSELL);
        $this->deleteLinks($productIdsToDeleteUpSellProducts,
            \Magento\Catalog\Model\Product\Link::LINK_TYPE_UPSELL);

        $this->deleteConfigurableProductRelations($productIdsToDeleteConfigurableVariations);
    }

    /**
     * @param $connection
     * @param $productsIds
     */
    private function deleteLinks($productsIds, $linkType)
    {
        if (empty($productsIds)) {
            return;
        }

        $productLinksTable = $this->productLinkResourceModel->getTable('catalog_product_link');

        $this->connection->delete(
            $productLinksTable,
            ['product_id IN (?)' => $productsIds, 'link_type_id' => $linkType]
        );
    }

    private function productHasImagesChanges($product)
    {
        foreach ($this->imagesManager->getImportArrayKeysContainingImagesChanges() as $key) {
            if (array_key_exists($key, $product)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $productIds
     */
    private function deleteConfigurableProductRelations($productIds)
    {
        if(empty($productIds)) {
            return;
        }

        $this->connection->delete(
            'catalog_product_relation',
            $this->connection->quoteInto('parent_id IN (?)', $productIds)
        );

        $this->connection->delete(
            'catalog_product_super_link',
            $this->connection->quoteInto('parent_id IN (?)', $productIds)
        );
    }

    /**
     * @param $productIds
     */
    private function deleteCategoriesRelations($productIds)
    {
        if(empty($productIds)) {
            return;
        }

        $categoriesTableName = $this->productResourceModel->getProductCategoryTable();

        $this->connection->delete(
            $categoriesTableName,
            $this->connection->quoteInto('product_id IN (?)', $productIds)
        );
    }
}