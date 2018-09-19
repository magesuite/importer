<?php

namespace MageSuite\Importer\Services\File;

class Writer
{
    protected $fileHandler;

    public function __construct($filePath)
    {
        $this->fileHandler = fopen($filePath, "w");
        $this->lineNumber = 1;
    }

    /**
     * Writes line to a file. First line is inserted without EOL, every other line starts with EOL
     * @param $line
     */
    public function writeLine($line)
    {
        if($this->lineNumber > 1) {
            $line = PHP_EOL . $line;
        }

        fwrite($this->fileHandler, $line);

        $this->lineNumber++;
    }

    public function __destruct() {
        fclose($this->fileHandler);
    }
}