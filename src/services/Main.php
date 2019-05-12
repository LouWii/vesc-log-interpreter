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
use louwii\vescloginterpreter\models\ChartData;
use louwii\vescloginterpreter\models\ChartDataSet;
use louwii\vescloginterpreter\models\DataTypeCollection;
use louwii\vescloginterpreter\models\ParsedData;

use yii\base\Component;

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
     * Parse the entire Vesc log file
     * 
     *      VescLogInterpreter::getInstance()->main->parseLogFile($filePath)
     *
     * @param string $filePath
     * @param boolean|integer $sumUp False to not sum up values, or integer value to reduce log to $sumUp points
     * @return mixed
     */
    public function parseLogFile($filePath, $sumUp = false)
    {
        $handle = fopen($filePath, "r");
        if ($handle) {
            $settings = array();
            $headers = array();
            $dataTypeCollection = new DataTypeCollection();
            $dataPoints = array();
            $lineCount = 0;
            $headersRead = false;

            while (($line = fgets($handle)) !== false) {
                // process the line read.
                if ($lineCount == 0 && substr($line, 0, 2) == '//') {
                    // Settings line
                    $settings = $this->parseSettings($line);
                } elseif (!$headersRead) {
                    // Treat this line as the headers for the data
                    $headers = $this->parseHeaders($line);
                    $headersRead = true;
                } else {
                    // Data row
                    $dataPoints[] = VescLogInterpreter::getInstance()->dataConverter->convertCsvToDataPoint($headers, $line);
                    VescLogInterpreter::getInstance()->dataConverter->addCsvDataToDataTypeCollection($headers, $line, $dataTypeCollection);
                }
            }
            fclose($handle);

            if (count($dataPoints) == 0) {
                return 'Couldn\'t find any row containing data.';
            }

            // ChartJS seems to have issues with too many values
            // We're dividing data into multiple arrays/parts
            // $sliced = FALSE;
            // $maxPerSlice = 2000;
            // if (count($xAxisLabels) > $maxPerSlice && (count($xAxisLabels)-$maxPerSlice) > 200)
            // {
            //     $sliced = TRUE;
            //     $slicedXAxisLabels = array();
            //     $slicedValues = array();
            //     $offset = 0;
            //     while( count( array_slice($xAxisLabels, $offset, $maxPerSlice) ) > 0)
            //     {
            //         $slicedXAxisLabels[] = array_slice($xAxisLabels, $offset, $maxPerSlice);
            //         $slicedValues[] = array_slice($values, $offset, $maxPerSlice);
            //         $offset += $maxPerSlice;
            //     }
            //     // $xAxisLabels = array_slice($xAxisLabels, 0, 4000);
            //     // $values = array_slice($values, 0, 4000);
            //     $xAxisLabels = $slicedXAxisLabels;
            //     $values = $slicedValues;
            // }

            // if ($sliced)
            // {
            //     $datasets = array();
            //     foreach ($values as $valuesPart)
            //     {
            //         $datasets[] = $this->createDataSets($headers, $valuesPart);
            //     }
            // }
            // else
            // {
            //     $datasets = $this->createDataSets($headers, $values);
            //     $xAxisLabels = array($xAxisLabels);
            //     $datasets = array($datasets);
            // }

            $chartData = VescLogInterpreter::getInstance()->dataConverter->convertDataTypeCollectionToChartJS($dataTypeCollection);

            $parsedData = new ParsedData();
            $parsedData->setXAxisLabels($chartData['xAxisLabels']);
            $parsedData->setDataSets($chartData['datasets']);
            $parsedData->setMaxValues($dataTypeCollection->getMaxValues());
            $parsedData->setMinValues($dataTypeCollection->getMinValues());

            return $parsedData;
        } else {
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
        foreach ($settingsParts as $settingItem) {
            $itemParts = explode('=', $settingItem);
            if (count($itemParts) == 2) {
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
        foreach ($headersParts as $headerItem) {
            $headers[] = $headerItem;
        }

        return $headers;
    }

    public function createDataSets($headers, $values)
    {
        $dataSets = array();

        $dataSetsNotWanted = array('Time', 'TimePassedInMs');

        foreach ($headers as $header) {
            if (!in_array($header, $dataSetsNotWanted)) {
                $dataSet = new ChartDataSet();
                $dataSet->label = $header;

                $dataSets[$header] = $dataSet;
            }
        }

        foreach ($values as $value) {
            foreach ($headers as $header) {
                if (!in_array($header, $dataSetsNotWanted)) {
                    $dataSets[$header]->data[] = $value[$header];
                }
            }
        }

        return $dataSets;
    }
}
