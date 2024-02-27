<?php

namespace MageSuite\Importer\Model\Import\Adapter;

// phpcs:disable Magento2.Functions.DiscouragedFunction.Discouraged
class FileAdapter extends \Magento\ImportExport\Model\Import\AbstractSource
{
    protected $fileHandler;
    protected $position = 0;
    protected $numberOfLines = 0;
    protected $filePath = '';

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
        $this->position = 0;
        $this->fileHandler = new \SplFileObject($filePath);
        $this->numberOfLines = $this->getLastNotEmptyLine();
        $this->fileHandler->seek(0);
        $colNames = array_keys(json_decode($this->fileHandler->current(), true));
        $this->fileHandler = fopen($filePath, 'r');

        parent::__construct($colNames);
    }

    /**
     * Go to given position and check if it is valid
     *
     * @param int $position
     * @return void
     * @throws \OutOfBoundsException
     */
    public function seek($position)
    {
        $this->position = $position;

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
        $this->position = 0;
    }

    /**
     * Get data at current position
     *
     * @return mixed
     */
    public function current()
    {
        if ($this->position == 0) {
            $this->fileHandler = fopen($this->filePath, 'r');
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
        return $this->position;
    }

    /**
     * Set pointer to next position
     *
     * @return void
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * Is current position valid?
     *
     * @return bool
     */
    public function valid()
    {
        return $this->position <= $this->numberOfLines;
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
        if (!is_array($values)) {
            throw new \InvalidArgumentException('array expected');
        }

        foreach ($values as $key => $value) {
            if (!is_array($value)) {
                continue;
            }

            $variations = [];

            foreach ($value as $row) {
                $attributes = [];

                foreach ($row as $attributeName => $attributeValue) {
                    $attributes[] = sprintf("%s=%s", $attributeName, $attributeValue);
                }

                $variations[] = implode(',', $attributes);
            }

            $values[$key] = implode('|', $variations);
        }

        return $values;
    }

    protected function getLastNotEmptyLine()
    {
        $this->fileHandler->seek(PHP_INT_MAX);
        $this->fileHandler->seek($this->fileHandler->key());

        while (empty(str_replace([PHP_EOL, "\r"], "", $this->fileHandler->current()))) {
            $this->fileHandler->seek($this->fileHandler->key()-1);
        }

        $lastNotEmptyLine = $this->fileHandler->key();

        return $lastNotEmptyLine;
    }
}
