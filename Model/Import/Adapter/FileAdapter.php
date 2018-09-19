<?php

namespace MageSuite\Importer\Model\Import\Adapter;

class FileAdapter extends \Magento\ImportExport\Model\Import\AbstractSource
{
    private $fileHandler;

    /**
     * @var int
     */
    private $_position = 0;

    private $numberOfLines = 0;

    private $filePath = '';

    public function __construct($filePath)
    {
        $this->filePath = $filePath;

        $this->_position = 0;

        $this->fileHandler = new \SplFileObject($filePath);

        $this->fileHandler->seek(PHP_INT_MAX);

        $this->numberOfLines = $this->fileHandler->key();

        $this->fileHandler->seek(0);

        $colNames = array_keys(json_decode($this->fileHandler->current(), true));

        $this->fileHandler = fopen($filePath,'r');

        parent::__construct($colNames);
    }

    /**
     * Go to given position and check if it is valid
     *
     * @throws \OutOfBoundsException
     * @param int $position
     * @return void
     */
    public function seek($position)
    {
        $this->_position = $position;


        if (!$this->valid()) {
            throw new \OutOfBoundsException("invalid seek position ($position)");
        }
    }

    /**
     * Rewind to starting position
     *
     * @return void
     */
    public function rewind()
    {
        $this->_position = 0;

    }

    /**
     * Get data at current position
     *
     * @return mixed
     */
    public function current()
    {
        if($this->_position == 0) {
            $this->fileHandler = fopen($this->filePath,'r');
        }

        $values = json_decode(fgets($this->fileHandler), true);

        $values = $this->convertArrayToString($values);

        return $values;
    }

    /**
     * Get current position
     *
     * @return int
     */
    public function key()
    {
        return $this->_position;
    }

    /**
     * Set pointer to next position
     *
     * @return void
     */
    public function next()
    {
        ++$this->_position;
    }

    /**
     * Is current position valid?
     *
     * @return bool
     */
    public function valid()
    {
        return $this->_position <= $this->numberOfLines;
    }

    /**
     * Render next row
     *
     * Return array or false on error
     *
     * @return array|false
     */
    protected function _getNextRow()
    {
        $this->next();

        return $this->current();
    }

    protected function convertArrayToString($values)
    {
        foreach($values as $key => $value) {
            if(!is_array($value)) {
                continue;
            }

            $variations = [];

            foreach($value as $row) {
                $attributes = [];

                foreach($row as $attributeName => $attributeValue) {
                    $attributes[] = sprintf("%s=%s", $attributeName, $attributeValue);
                }

                $variations[] = implode(',', $attributes);
            }

            $values[$key] = implode('|', $variations);
        }

        return $values;
    }
}