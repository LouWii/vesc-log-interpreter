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
    protected $maxFileLineCount = 30000;

    // Public Methods
    // =========================================================================

    /**
     * Parse the entire Vesc log file
     * 
     * VescLogInterpreter::getInstance()->main->parseLogFile($filePath)
     *
     * @param string $filePath
     * @param bool $processGeoloc
     * @return mixed
     */
    public function parseLogFile(string $filePath, $processGeoloc = true)
    {
        $handle = fopen($filePath, "r");
        if ($handle) {
            $settings = array();
            $headers = array();
            $dataTypeCollection = new DataTypeCollection();
            $dataPoints = array();
            $lineCount = 0;
            $lastLine = null;
            $headersRead = false;
            $dateTimeStart = null;
            $dateTimeEnd = null;
            $geoloc = array();

            while (($line = fgets($handle)) !== false) {
                if ($lineCount > $this->maxFileLineCount) {
                    break;
                }

                // process the line read.
                if ($lineCount == 0 && substr($line, 0, 2) == '//') {
                    // Settings line
                    $settings = $this->parseSettings($line);
                } elseif (!$headersRead) {
                    // Treat this line as the headers for the data
                    $headers = $this->parseHeaders($line);
                    $headersRead = true;
                } else {
                    // DataPoints are currently not used
                    // Mainly because the format makes it harder to convert for ChartJS usage
                    // But keeping the logic for now
                    // $dataPoints[] = VescLogInterpreter::getInstance()->dataConverter->convertCsvToDataPoint($headers, $line);

                    VescLogInterpreter::getInstance()->dataConverter->addCsvDataToDataTypeCollection($headers, $line, $dataTypeCollection);

                    if ($dateTimeStart == null) {
                        $dateTimeStart = VescLogInterpreter::getInstance()->dataConverter->getDateTimeFromCsv($headers, $line);
                    }

                    if ($processGeoloc) {
                        $coordinates = VescLogInterpreter::getInstance()->dataConverter->getCoordinatesFromCsv($headers, $line);
                        if ($coordinates) {
                            $geoloc[] = $coordinates;
                        }
                    }

                    if (strlen($line) > 5) {
                        $lastLine = $line;
                    }
                }
                $lineCount++;
            }
            fclose($handle);

            // Use last line to get last DateTime
            $dateTimeEnd = VescLogInterpreter::getInstance()->dataConverter->getDateTimeFromCsv($headers, $lastLine);

            if (!$dataTypeCollection->hasValues()) {
                throw new \Exception('Couldn\'t find any row containing data.');
            }

            // Reduce our data to 1 value per second
            // $dataTypeCollection->reduceAllDataTypeDataPerSecond();
            VescLogInterpreter::getInstance()->dataCleaner->reduceDataInCollectionToOnePerSecond($dataTypeCollection);

            $dataTypeCollection->checkDataIntegrity();

            $chartData = VescLogInterpreter::getInstance()->dataConverter->convertDataTypeCollectionToChartJS($dataTypeCollection);

            $parsedData = new ParsedData();
            $parsedData->setXAxisLabels($chartData['xAxisLabels']);
            $parsedData->setDataSets($chartData['datasets']);
            $parsedData->setMaxValues($dataTypeCollection->getMaxValues());
            $parsedData->setMinValues($dataTypeCollection->getMinValues());
            $parsedData->setAverageValues($dataTypeCollection->getAverageValues());

            if ($dateTimeStart != null && $dateTimeEnd != null) {
                $duration = $dateTimeStart->diff($dateTimeEnd);
                $parsedData->setDuration($duration);
            }

            if (count($geoloc) > 0) {
                $geoloc = VescLogInterpreter::getInstance()->dataCleaner->reduceGeolocToOnePerSecond($geoloc);
                $parsedData->setGeolocation($geoloc);
            }

            return $parsedData;
        } else {
            throw new \Exception('Couldn\'t read the file after upload.');
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
}
