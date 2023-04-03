<?php

namespace MageSuite\Importer\Model\Import\Data;

class Iterator implements \Iterator
{
    protected \Magento\Framework\DB\Adapter\AdapterInterface $connection;
    protected \Magento\Framework\App\ResourceConnection $resource;
    protected $rowsCount;
    protected $index = 0;
    protected $lastId = null;

    public function __construct(\Magento\Framework\App\ResourceConnection $resource)
    {
        $this->resource = $resource;
        $this->connection = $resource->getConnection(
            \Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION
        );
        $this->rowsCount = $this->getRowsCount();
    }

    public function recalculateRowsCount()
    {
        $this->rowsCount = $this->getRowsCount();
    }

    public function getLastBunchId()
    {
        return $this->lastId;
    }

    public function getRowsCount()
    {
        $select = $this->connection->select()->from(
            $this->connection->getTableName('importexport_importdata'),
            ['cnt' => 'count(*)']
        );

        return $this->connection->fetchOne($select);
    }

    public function current(): mixed
    {
        $select = $this->connection
            ->select()
            ->from($this->connection->getTableName('importexport_importdata'), ['id', 'data'])
            ->order('id ASC')
            ->limit(1, $this->index);

        $stmt = $this->connection->query($select);
        $row = $stmt->fetch();

        if (empty($row)) {
            return false;
        }

        $this->lastId = $row['id'];
        return [$row['data']];
    }

    public function next(): void
    {
        $this->index++;
    }

    public function previous()
    {
        $this->index--;
    }

    public function key(): mixed
    {
        return $this->index;
    }

    public function valid(): bool
    {
        return $this->index < $this->rowsCount;
    }

    public function rewind(): void
    {
        $this->index = 0;
    }
}
