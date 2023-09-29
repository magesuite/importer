<?php

namespace MageSuite\Importer\Model\Import\Data;

class Iterator implements \Iterator
{
    protected \Magento\Framework\DB\Adapter\AdapterInterface $connection;
    protected \Magento\Framework\App\ResourceConnection $resource;
    protected ?int $maxId = null;
    protected int $lastId = 0;

    public function __construct(\Magento\Framework\App\ResourceConnection $resource)
    {
        $this->resource = $resource;
        $this->connection = $resource->getConnection(
            \Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION
        );
        $this->maxId = $this->getMaxId();
    }

    public function getLastBunchId(): int
    {
        return $this->lastId;
    }

    public function getMaxId(): int
    {
        if ($this->maxId === null) {
            $select = $this->connection->select()->from(
                $this->connection->getTableName('importexport_importdata'),
                ['MAX(id)']
            );
            $this->maxId = (int)$this->connection->fetchOne($select);
        }

        return $this->maxId;
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
        return $this->getMaxId() > 0 && $this->lastId <= $this->getMaxId();
    }

    public function rewind(): void
    {
        $this->lastId = 0;
    }

    public function resetMaxId(): void
    {
        $this->maxId = null;
    }
}
