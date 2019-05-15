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

use louwii\vescloginterpreter\models\ChartDataSet;
use louwii\vescloginterpreter\models\DataPoint;
use louwii\vescloginterpreter\models\DataTypeCollection;

use yii\base\Component;

/**
 * DataConverter Service
 *
 * @author    Louwii
 * @package   VescLogInterpreter
 * @since     1.0.0
 */
class DataConverter extends Component
{
    /**
     * "Convert" a line of a CSV file to a DataPoint object
     */
    public function convertCsvToDataPoint(
        array $headers, string $csvLine, array $ignore = array('TimePassedInMs', 'Latitude', 'Longitude'))
    {
        $dataRow = array();
        $dataRowParts = explode(',', $csvLine);
        if (count($dataRowParts) == count($headers)) {
            // First pass, get time and convert values
            foreach ($headers as $idx => $header) {
                if (!in_array($header, $ignore)) {
                    if ($header != 'Time') {
                        $dataRow[$header] = floatval($dataRowParts[$idx]);
                    } else {
                        $dataRow[$header] = $dataRowParts[$idx];
                    }
                }
            }

            $dataPoint = new DataPoint();
            $dataPoint->setDateTime($this->formatCsvDateTime($dataRow['Time']));

            // Second pass, add all values to the Point
            foreach ($dataRow as $header => $value) {
                if ($header != 'Time') {
                    $dataPoint->addValue($header, $value);
                }
            }

            return $dataPoint;
        } else {
            throw new Exception('Got '.count($headers).' headers and '.count($dataRowParts).' data items, cannot parse data');
        }
    }

    /**
     * Convert an array of DataPoint to data formatted for ChartJS lib
     * Data is composed of
     * - n labels for the X axis (date times)
     * - list of value types (TempPcb, Speed, MotorCurrent...). Each value type contains an array of n values
     */
    public function convertDataPointsToChartJS(array $dataPoints)
    {
        $xAxisLabels = array();
        $dataSets = array();
        $dataTimeFormatXAxis = 'Y-m-d H:i:s.u';
        $valuesForTypes = array();

        // This is where dataPoint is a problem.
        // We need to create array of all values for each type
        // But a dataPoint is 1 value for each type
        // So we need to loop through to group all values per type
        foreach ($dataPoints as $dataPoint) {
            $xAxisLabels[] = $dataPoint->getDateTime()->format($dataTimeFormatXAxis);

            foreach ($dataPoint->getValues() as $valueType => $value) {
                if (!array_key_exists($valueType, $valuesForTypes)) {
                    $valuesForTypes[$valueType] = array();
                }
                $valuesForTypes[$valueType][] = $value;
            }
        }

        foreach ($valuesForTypes as $type => $values) {
            $dataSet = new ChartDataSet();
            $dataSet->label = $type;
            $dataSet->data = $values;

            $dataSets[$type] = $dataSet;
        }

        // xAxisLabels needs to be an array of array (because there can be multiple x axis labels?)
        return array('xAxisLabels' => array($xAxisLabels), 'datasets' => array($dataSets));
    }

    /**
     * Parse line from CSV file and add values to a DataTypeCollection
     */
    public function addCsvDataToDataTypeCollection(array $headers, string $csvLine, DataTypeCollection $dataTypeCollection, array $ignore = array('TimePassedInMs', 'Latitude', 'Longitude'))
    {
        $dataRowParts = explode(',', $csvLine);
        if (count($dataRowParts) == count($headers)) {
            
            foreach ($headers as $idx => $header) {
                if (!in_array($header, $ignore)) {
                    if ($header != 'Time') {
                        // TODO: casting to float is not correct for some value types
                        $dataTypeCollection->addValueToDataType($header, floatval($dataRowParts[$idx]));
                    } else {
                        $formatedDateTime = $this->formatCsvDateTime($dataRowParts[$idx]);
                        $dataTypeCollection->addValueToDataType($header, $formatedDateTime);
                    }
                }
            }
        }
    }

    public function convertDataTypeCollectionToChartJS(DataTypeCollection $dataTypeCollection)
    {
        $dataSets = array();
        $xAxisLabels = $dataTypeCollection->getDataType('Time')->getValues();

        foreach ($dataTypeCollection->getDataTypes() as $dataType) {
            if ($dataType->getName() != 'Time') {
                $dataSet = new ChartDataSet();
                $dataSet->label = $dataType->getName();
                $dataSet->data = $dataType->getValues();

                $dataSets[$dataType->getName()] = $dataSet;
            }
        }

        return array('xAxisLabels' => array($xAxisLabels), 'datasets' => array($dataSets));
    }

    public function getDateTimeFromCsv(array $headers, string $csvLine)
    {
        $dataRowParts = explode(',', $csvLine);
        if (count($dataRowParts) == count($headers)) {
            foreach ($headers as $idx => $header) {
                if ($header == 'Time') {
                    return new \DateTime($this->formatCsvDateTime($dataRowParts[$idx]));
                }
            }
        }

        return null;
    }

    public function getCoordinatesFromCsv(array $headers, string $csvLine)
    {
        $latitude = null;
        $longitude = null;
        $timeStr = null;
        $dataRowParts = explode(',', $csvLine);
        if (count($dataRowParts) == count($headers)) {
            foreach ($headers as $idx => $header) {
                if ($header == 'Time') {
                    $timeStr = $this->formatCsvDateTime($dataRowParts[$idx]);
                } elseif ($header == 'Latitude') {
                    $latitude = floatval($dataRowParts[$idx]);
                } elseif ($header == 'Longitude') {
                    $longitude = floatval($dataRowParts[$idx]);
                }
            }
        }

        if ($latitude && $longitude && $timeStr) {
            return array(
                'timeStr' => $timeStr,
                'coordinates' => array(
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                )
            );
        }

        return null;
    }

    /**
     * Convert a date & time string from CSV to a properly formatted string for DateTime object
     * '22_01_2018_22_43_04.657' to '2018-01-22 22:43:04.657'
     * @param string $csvDateTime The time string from VESC csv file
     */
    public function formatCsvDateTime(string $csvDateTime)
    {
        $timeParts = explode('_', $csvDateTime);
        return $timeParts[2].'-'.$timeParts[1].'-'.$timeParts[0].' '.$timeParts[3].':'.$timeParts[4].':'.$timeParts[5];
    }
}