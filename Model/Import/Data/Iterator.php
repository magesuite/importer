<?php

namespace MageSuite\Importer\Model\Import\Data;

class Iterator implements \Iterator
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var \Magento\Framework\App\Resource
     */
    private $resource;

    protected $rowsCount;

    protected $index = 0;

    public function __construct(\Magento\Framework\App\ResourceConnection $resource)
    {
        $this->resource = $resource;
        $this->connection = $resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);

        $this->rowsCount = $this->getRowsCount();
    }

    public function getRowsCount() {
        $select = $this->connection->select()->from(
            'importexport_importdata',
            ['cnt' => 'count(*)']
        );

        return $this->connection->fetchOne($select);
    }

    public function current()
    {
        $select = $this->connection
            ->select()
            ->from('importexport_importdata', ['data'])
            ->order('id ASC')
            ->limit(1, $this->index);

        $stmt = $this->connection->query($select);
        $row = $stmt->fetch();

        return [$row['data']];
    }

    public function next()
    {
        $this->index++;
    }

    public function key()
    {
        return $this->index;
    }

    public function valid()
    {
        return $this->index < $this->rowsCount;
    }

    public function rewind()
    {
        $this->index = 0;
    }
}