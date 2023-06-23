<?php

namespace MageSuite\Importer\Model\Import\Data;

class Iterator implements \Iterator
{
    protected \Magento\Framework\DB\Adapter\AdapterInterface $connection;
    protected \Magento\Framework\App\ResourceConnection $resource;
    protected int $maxId = 0;
    protected int $lastId = 0;

    public function __construct(\Magento\Framework\App\ResourceConnection $resource)
    {
        $this->resource = $resource;
        $this->connection = $resource->getConnection(
            \Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION
        );
        $this->maxId = $this->getMaxId();
    }

    public function getLastBunchId()
    {
        return $this->lastId;
    }

    public function getMaxId()
    {
        $select = $this->connection->select()->from(
            $this->connection->getTableName('importexport_importdata'),
            ['max' => 'MAX(id)']
        );

        return $this->connection->fetchOne($select);
    }

    public function current(): mixed
    {
        $select = $this->connection
            ->select()
            ->from($this->connection->getTableName('importexport_importdata'), ['id', 'data'])
            ->order('id ASC')
            ->where('id >= ?', $this->key())
            ->limit(1);

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
        $this->lastId++;
    }

    public function previous(): void
    {
        $this->lastId--;
    }

    public function key(): mixed
    {
        return $this->lastId;
    }

    public function valid(): bool
    {
        return $this->lastId <= $this->maxId;
    }

    public function rewind(): void
    {
        $this->lastId = 0;
    }
}
