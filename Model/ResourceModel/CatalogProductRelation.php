<?php

declare(strict_types=1);

namespace MageSuite\Importer\Model\ResourceModel;

class CatalogProductRelation
{
    protected \Magento\Framework\App\ResourceConnection $resourceConnection;
    protected \Magento\Framework\DB\Adapter\AdapterInterface $connection;
    protected \Magento\InventoryCatalogApi\Model\GetProductIdsBySkusInterface $getProductIdsBySkus;
    protected \Magento\Framework\EntityManager\MetadataPool $metadataPool;
    protected ?string $productEntityLinkField = null;

    public function __construct(
        \Magento\CatalogImportExport\Model\Import\Product $entityModel,
        \Magento\InventoryCatalogApi\Model\GetProductIdsBySkusInterface $getProductIdsBySkus,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->connection = $resourceConnection->getConnection();
        $this->metadataPool = $metadataPool;
    }

    public function getExistingRelations(array $parentSkus): array
    {
        $linkField = $this->getProductEntityLinkField();
        $select = $this->connection->select();

        $select->from(['cpr' => 'catalog_product_relation']);
        $select->joinLeft(['cpep' => 'catalog_product_entity'], sprintf('cpep.%s = cpr.parent_id', $linkField), ['parent_sku' => 'sku']);
        $select->joinLeft(['cpec' => 'catalog_product_entity'], 'cpec.entity_id = cpr.child_id', ['child_sku' => 'sku']);
        $select->where('cpep.sku IN (?)', $parentSkus);

        $results = $this->connection->fetchAll($select);

        if (empty($results)) {
            return [];
        }

        $relations = [];

        foreach ($results as $result) {
            if (!isset($relations[$result['parent_sku']])) {
                $relations[$result['parent_sku']] = [];
            }

            $relations[$result['parent_sku']][$result['child_sku']] = $result;
        }

        return $relations;
    }

    public function deleteRelations(array $parentToChildToDelete): void
    {
        $wheresCatalogProductRelation = [];
        $wheresCatalogProductSuperLink = [];

        foreach ($parentToChildToDelete as $parentProductId => $childIds) {
            $wheresCatalogProductRelation[] = implode(' AND ', [
                $this->connection->quoteInto('parent_id = ?', $parentProductId, \Zend_Db::INT_TYPE),
                $this->connection->quoteInto('child_id IN (?)', $childIds, \Zend_Db::INT_TYPE)
            ]);
            $wheresCatalogProductSuperLink[] = implode(' AND ', [
                $this->connection->quoteInto('parent_id = ?', $parentProductId, \Zend_Db::INT_TYPE),
                $this->connection->quoteInto('product_id IN (?)', $childIds, \Zend_Db::INT_TYPE)
            ]);
        }

        if (empty($wheresCatalogProductRelation)) {
            return;
        }

        $this->connection->delete(
            $this->connection->getTableName('catalog_product_relation'),
            implode(' OR ', $wheresCatalogProductRelation)
        );
        $this->connection->delete(
            $this->connection->getTableName('catalog_product_super_link'),
            implode(' OR ', $wheresCatalogProductSuperLink)
        );
    }

    protected function getProductEntityLinkField(): string
    {
        if (!$this->productEntityLinkField) {
            $this->productEntityLinkField = $this->metadataPool
                ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
                ->getLinkField();
        }

        return $this->productEntityLinkField;
    }
}
