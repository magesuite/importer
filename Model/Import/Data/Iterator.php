<?php

namespace MageSuite\Importer\Model\Import\Data;

class Iterator implements \Iterator
{
    protected \Magento\Framework\DB\Adapter\AdapterInterface $connection;

    protected int $rowsTotal;

    protected int $index = 0;

    protected ?int $lastId = null;

    public function __construct(\Magento\Framework\App\ResourceConnection $resource)
    {
        $this->connection = $resource->getConnection();
        $this->rowsTotal = $this->getRowsTotal();
    }

    public function recalculateRowsTotal(): void
    {
        $this->rowsTotal = $this->getRowsTotal();
    }

    public function getLastBunchId(): int
    {
        return $this->lastId;
    }

    public function getRowsTotal(): int
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

    public function previous(): void
    {
        $this->index--;
    }

    public function key(): mixed
    {
        return $this->index;
    }

    public function valid(): bool
    {
        return $this->index < $this->rowsTotal;
    }

    public function rewind(): void
    {
        $this->index = 0;
    }
}
