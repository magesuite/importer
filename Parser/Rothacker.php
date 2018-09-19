<?php

namespace MageSuite\Importer\Parser;

class Rothacker implements \MageSuite\Importer\Command\Parser
{

    /**
     * Parses input files and outputs unified file
     * @param $configuration
     * @return mixed
     */
    public function parse($configuration)
    {
        $configuration['source_path'] = BP . DIRECTORY_SEPARATOR . $configuration['source_path'];
        $configuration['target_path'] = BP . DIRECTORY_SEPARATOR . $configuration['target_path'];

        if(!isset($configuration['last_line_number'])) {
            $configuration['last_line_number'] = PHP_INT_MAX;
        }

        if(!file_exists($configuration['source_path'])) {
            throw new \InvalidArgumentException("Source file %s does not exist", $configuration['source_path']);
        }

        $sourceFileHandle = fopen($configuration['source_path'], "r");
        $targetFileHandle = fopen($configuration['target_path'], "w");

        if ($sourceFileHandle) {
            $lineNumber = 1;
            $firstLine = true;

            while (($line = fgets($sourceFileHandle)) !== false) {
                if ($lineNumber == 1) {
                    $fields = str_getcsv($line, "|");
                } else {
                    $line = str_getcsv($line, "|");

                    $row = $this->prepareRow($fields, $line, $lineNumber);

                    fwrite($targetFileHandle, !$firstLine ? PHP_EOL . json_encode($row) : json_encode($row));

                    $firstLine = false;
                }

                if ($lineNumber == $configuration['last_line_number']) {
                    break;
                }

                $lineNumber++;
            }
        }

        fclose($sourceFileHandle);
    }

    /**
     * @param $fields
     * @param $line
     * @param $lineNumber
     * @return array
     */
    private function prepareRow($fields, $line, $lineNumber)
    {
        $row = [
            'product_type' => 'simple',
            'attribute_set_code' => 'Book',
        ];

        $attributesAdditional = [
            'nr_of_images',
            'nr_of_tables',
            'nr_of_pages',
            'nr_of_data',
            'nr_of_dvd',
            'nr_of_video',
            'nr_of_volumes',
            'nr_of_tables',
            'nr_of_tables',
            'ranking',
            'update_status_text',
            'available_code',
            'inventory',
            'date_of_publication',
            'delivery_time',
            'offer_price_description',
            'author',
            'edition',
            'edition_text',
            'foreign_article',
            'isbn',
            'isbn_short',
            'issuer',
            'language',
            'binding',
            'medium',
            'desc_images',
            'desc_tables',
            'manufacturer',
            'book_nonbook',
            'subtitle',
            'author_text',
            'recension',
            'table_of_contents',
            'author1',
            'author2',
            'author3',
            'spiegel_belletristik_ranking',
            'spiegel_sachbuch_ranking',
            'leseproben_datei',
            'specification',
            'marketing_price_text',
            'special_article',
            'client',
        ];

        $values = [];

        foreach ($fields as $index => $field) {
            $values[$field] = $line[$index];
        }

        unset($row['tax_class_id']);

        $row['url_key'] = $lineNumber;
        $row['price'] = $values['price'];
        $row['name'] = $values['name'];
        $row['sku'] = $values['sku'];
        $row['categories'] = 'Default Category/Books';
        $row['qty'] = '10';
        $row['is_in_stock'] = 1;
        $row['product_websites'] = 'base';
        $row['store_view_code'] = 'default';

        foreach ($attributesAdditional as $attribute) {
            $row[$attribute] = $values[$attribute];
        }
        return $row;
    }
}