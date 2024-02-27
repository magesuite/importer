<?php

namespace MageSuite\Importer\Model\ResourceModel;

class CatalogProductRelation
{
    protected \Magento\Framework\App\ResourceConnection $resourceConnection;
    protected \Magento\Framework\DB\Adapter\AdapterInterface $connection;
    protected \Magento\InventoryCatalogApi\Model\GetProductIdsBySkusInterface $getProductIdsBySkus;

    public function __construct(
        \Magento\CatalogImportExport\Model\Import\Product $entityModel,
        \Magento\InventoryCatalogApi\Model\GetProductIdsBySkusInterface $getProductIdsBySkus,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->connection = $resourceConnection->getConnection();
    }

    public function getExistingRelations(array $parentSkus): array
    {
        $select = $this->connection->select();

        $select->from(['cpr' => 'catalog_product_relation']);
        $select->joinLeft(['cpep' => 'catalog_product_entity'], 'cpep.entity_id = cpr.parent_id', ['parent_sku' => 'sku']);
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

            $relations[$result['parent_sku']][] = $result['child_sku'];
        }

        return $relations;
    }

    public function deleteRelations(array $parentToChildToDelete): void
    {
        $wheresCatalogProductRelation = [];
        $wheresCatalogProductSuperLink = [];

        foreach ($parentToChildToDelete as $parentProductId => $childIds) {
            $wheresCatalogProductRelation[] = sprintf('(parent_id = %d AND child_id IN(%s))', $parentProductId, implode(',', $childIds));
            $wheresCatalogProductSuperLink[] = sprintf('(parent_id = %d AND product_id IN(%s))', $parentProductId, implode(',', $childIds));
        }

        if (empty($wheresCatalogProductRelation)) {
            return;
        }

        $this->connection->delete($this->connection->getTableName('catalog_product_relation'), implode(' OR ', $wheresCatalogProductRelation));
        $this->connection->delete($this->connection->getTableName('catalog_product_super_link'), implode(' OR ', $wheresCatalogProductSuperLink));
    }
}
