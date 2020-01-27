<?php

namespace MageSuite\Importer\Parser;

class Generic implements \MageSuite\Importer\Command\Parser
{
    /**
     * @var \Creativestyle\CSV\File\CsvReader
     */
    protected $csvReader;

    public function __construct(\Creativestyle\CSV\File\CsvReader $csvReader)
    {
        $this->csvReader = $csvReader;
    }

    /**
     * Parses input files and outputs unified file
     * @param $configuration
     * @return mixed
     */
    public function parse($configuration)
    {
        $configuration['source_path'] = BP . DIRECTORY_SEPARATOR . $configuration['source_path'];
        $configuration['target_path'] = BP . DIRECTORY_SEPARATOR . $configuration['target_path'];

        $targetFileWriter = $this->getFileWriter($configuration['target_path']);

        foreach ($this->csvReader->getLinesFromFile($configuration['source_path'], $configuration['delimiter']) as $row) {
            $line = json_encode($row);

            $targetFileWriter->writeLine($line);
        }
    }

    protected function getFileWriter($targetFilePath)
    {
        return new \MageSuite\Importer\Services\File\Writer($targetFilePath);
    }
}
