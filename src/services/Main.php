<?php
/**
 * VESC Log Interpreter plugin for Craft CMS 3.x
 *
 * Process log data from VESC Monitor to generate charts
 *
 * @link      http://github.com/louwii
 * @copyright Copyright (c) 2018 Louwii
 */

namespace louwii\vescloginterpreter\services;

use louwii\vescloginterpreter\VescLogInterpreter;
use louwii\vescloginterpreter\models\ChartDataSet;

use Craft;
use craft\base\Component;

/**
 * Main Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Louwii
 * @package   VescLogInterpreter
 * @since     1.0.0
 */
class Main extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     VescLogInterpreter::$plugin->main->exampleService()
     *
     * @return mixed
     */
    public function exampleService()
    {
        $result = 'something';

        return $result;
    }

    /**
     * Parse the entire Vesc log file
     * 
     *      VescLogInterpreter::$plugin->main->parseLogFile($filePath)
     *
     * @param string $filePath
     * @return mixed
     */
    public function parseLogFile($filePath)
    {
        $handle = fopen($filePath, "r");
        if ($handle)
        {
            $settings = array();
            $headers = array();
            $values = array();
            $lineCount = 0;
            $headersRead = FALSE;

            while (($line = fgets($handle)) !== false) {
                // process the line read.
                if ($lineCount == 0 && substr($line, 0, 2) == '//')
                {
                    // Settings line
                    $settings = $this->parseSettings($line);
                }
                elseif (!$headersRead)
                {
                    // Treat this line as the headers for the data
                    $headers = $this->parseHeaders($line);
                    $headersRead = TRUE;
                }
                else
                {
                    // Data row
                    $values[] = $this->parseData($headers, $line);
                }
            }
            fclose($handle);

            $datasets = $this->createDataSets($headers, $values);

            return $datasets;
        }
        else
        {
            return 'Couldn\'t read the file after upload.';
        }
    }

    /**
     * Parse the Settings row of the Vesc monitor log
     *
     * @param [string] $line
     * @return array
     */
    public function parseSettings($line)
    {
        $settings = array();
        $settingsParts = explode(',', $line);
        foreach ($settingsParts as $settingItem)
        {
            $itemParts = explode('=', $settingItem);
            if (count($itemParts) == 2)
            {
                $settings[str_replace(array('_', '//'), array(' ', ''), $itemParts[0])] = $itemParts[1];
            }
        }

        return $settings;
    }

    /**
     * Parse the Headers row of the Vesc monitor log
     *
     * @param string $line
     * @return array
     */
    public function parseHeaders($line)
    {
        $headers = array();
        $headersParts = explode(',', $line);
        foreach ($headersParts as $headerItem)
        {
            $headers[] = $headerItem;
        }

        return $headers;
    }

    /**
     * Parse a data row of the Vesc monitor log
     *
     * @param array $headers
     * @param string $line
     * @return array
     */
    public function parseData($headers, $line)
    {
        $dataRow = array();
        $dataRowParts = explode(',', $line);
        if (count($dataRowParts) == count($headers))
        {
            foreach ($headers as $idx => $header)
            {
                if ($header != 'Time')
                {
                    $dataRow[$header] = floatval($dataRowParts[$idx]);
                }
                else
                {
                    $dataRow[$header] = $dataRowParts[$idx];
                }
            }
        }
        else
        {
            throw new Exception('Got '.count($headers).' headers and '.count($dataRowParts).' data items, cannot parse data');
        }

        return $dataRow;
    }

    public function createDataSets($headers, $values)
    {
        $dataSets = array();

        $dataSetsNotWanted = array('Time', 'TimePassedInMs');

        foreach ($headers as $header)
        {
            if (!in_array($header, $dataSetsNotWanted))
            {
                $dataSet = new ChartDataSet();
                $dataSet->label = $header;

                $dataSets[$header] = $dataSet;
            }
        }

        foreach ($values as $value)
        {
            foreach ($headers as $header)
            {
                if (!in_array($header, $dataSetsNotWanted))
                {
                    $dataSets[$header]->data[] = $value[$header];
                }
            }
        }

        return $dataSets;
    }

    public function getDatasetsCacheId($timestamp)
    {
        return VescLogInterpreter::$plugin->name.'--datasets--'.$timestamp;
    }

    public function getErrorsCacheId($timestamp)
    {
        return VescLogInterpreter::$plugin->name.'--errors--'.$timestamp;
    }

    /**
     * Retrieve data from the cache
     *
     * @param [type] $timestamp
     * @return void
     */
    public function retrieveCachedData($timestamp)
    {
        $datasets = craft()->cache->get(__CLASS__.'--datasets--'.$timestamp);
        $errors = craft()->cache->get(__CLASS__.'--errors--'.$timestamp);

        return array(
            'datasets' => $datasets,
            'errors' => $errors
        );
    }
}
