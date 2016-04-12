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
        if (!file_exists($filename) || !is_readable($filename)) {
            return false;
        }

        $header = null;
        $data   = array();

        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, 100000, $separator)) !== false) {
                if (!$header) {
                    $header = $row;
                } else {
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }
        return $data;
    }

    public function convertPartial($filename, $separator = ',', $start = 0,
                                   $length = 100)
    {
        if (!file_exists($filename) || !is_readable($filename)) {
            return false;
        }
        $data   = array();
        $header = null;
        $handle = fopen($filename, 'r');
        if ($handle === false) {
            return $data;
        }

        $header = fgetcsv($handle, 100000, $separator); //get header first
        
        $i = 0;

        while (($row = fgetcsv($handle, 100000, $separator)) !== false) {

            

            if ($i < $start) {
                $i++;
                continue;
            }
            if ($i == $start + $length) {
                break;
            }
            $data[] = array_combine($header, $row);
            
            $i++;
        }
        fclose($handle);

        return $data;
    }
}