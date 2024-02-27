<?php

namespace MageSuite\Importer\Test\Unit\Model\Import\Adapter;

class FileAdapterTest extends \PHPUnit\Framework\TestCase
{
    const EXPECTED_ROWS_COUNT = 6;

    public function testItIgnoresEmptyLinesInTheEnd()
    {
        $fileAdapter = new \MageSuite\Importer\Model\Import\Adapter\FileAdapter(realpath(__DIR__.'/../../assets/file_with_empty_last_lines.json'));

        $rows = [];

        while ($fileAdapter->valid()) {
            $rows[] = $fileAdapter->current();
            $fileAdapter->next();
        }

        $this->assertCount(self::EXPECTED_ROWS_COUNT, $rows);

        for ($i = 1; $i <= 6; $i++) {
            $row = array_shift($rows);
            $this->assertEquals($i, $row['line_number']);
        }
    }
}
