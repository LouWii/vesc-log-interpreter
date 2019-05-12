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
use louwii\vescloginterpreter\models\DataTypeCollection;

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
    private $dataConverter;

    public function __construct()
    {
        $this->dataConverter = new DataConverter();
    }

    // Public Methods
    // =========================================================================

    
    /**
     * Parse the entire Vesc log file
     * 
     *      VescLogInterpreter::$plugin->main->parseLogFile($filePath)
     *
     * @param string $filePath
     * @param boolean|integer $sumUp False to not sum up values, or integer value to reduce log to $sumUp points
     * @return mixed
     */
    public function parseLogFile($filePath, $sumUp = false)
    {
        $handle = fopen($filePath, "r");
        if ($handle)
        {
            $settings = array();
            $headers = array();
            $dataTypeCollection = new DataTypeCollection();
            $dataPoints = array();
            $lineCount = 0;
            $headersRead = false;

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
                    $headersRead = true;
                }
                else
                {
                    // Data row
                    $dataPoints[] = $this->dataConverter->convertCsvToDataPoint($headers, $line);
                    $this->dataConverter->addCsvDataToDataTypeCollection($headers, $line, $dataTypeCollection);
                }
            }
            fclose($handle);

            if (count($dataPoints) == 0)
            {
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

            return $this->dataConverter->convertDataTypeCollectionToChartJS($dataTypeCollection);
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

    public function getAxisLabelsCacheId($timestamp)
    {
        return VescLogInterpreter::$plugin->name.'--axis_labels--'.$timestamp;
    }

    public function getErrorsCacheId($timestamp)
    {
        return VescLogInterpreter::$plugin->name.'--errors--'.$timestamp;
    }

    /**
     * Cache processed data with a unique ID, to be able to keep it for later
     *
     * @param int $timestamp
     * @param array $datasets
     * @param array $errors
     * @return bool True if caching worked, false otherwise
     */
    public function cacheData($timestamp, $axisLabels, $datasets, $errors)
    {
        // Cache processed data
        // Use https://yii2-cookbook.readthedocs.io/caching/
        // Use timestamp to create unique IDs and avoid collision if people submits at the same time
        if ($datasets != NULL && count($datasets) > 0)
        {
            // If datasets exist, keep the data for a week
            $cacheTTL = 60*60*24*7;
        }
        else
        {
            // If no dataset, then it means process failed, no need to keep it for long
            $cacheTTL = 3600;
        }

        return
            Craft::$app->cache->set(VescLogInterpreter::$plugin->main->getDatasetsCacheId($timestamp), $datasets, $cacheTTL)
            &&
            Craft::$app->cache->set(VescLogInterpreter::$plugin->main->getAxisLabelsCacheId($timestamp), $axisLabels, $cacheTTL)
            &&
            Craft::$app->cache->set(VescLogInterpreter::$plugin->main->getErrorsCacheId($timestamp), $errors, $cacheTTL)
            ;
    }

    /**
     * Retrieve data from the cache
     *
     * @param [type] $timestamp
     * @return void
     */
    public function retrieveCachedData($timestamp)
    {
        $xAxisLabels = Craft::$app->cache->get(VescLogInterpreter::$plugin->main->getAxisLabelsCacheId($timestamp));
        $datasets = Craft::$app->cache->get(VescLogInterpreter::$plugin->main->getDatasetsCacheId($timestamp));
        $errors = Craft::$app->cache->get(VescLogInterpreter::$plugin->main->getErrorsCacheId($timestamp));

        return array(
            'axisLabels' => $xAxisLabels,
            'datasets' => $datasets,
            'errors' => $errors
        );
    }
}
