<?php
namespace AppBundle\Services;

/**
 * Convert CSV file to an associative key => value array
 */
class CsvToArray
{
    public function __construct()
    {

    }

    public function convert($filename, $separator = ',')
    {
        if(!file_exists($filename) ||!is_readable($filename)) {
            return false;
        }

        $header = null;
        $data = array();

        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, 100000, $separator)) !== false) {
                if(!$header) {
                    $header = $row;
                    
                } else {
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }
        return $data;
    }
}

