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
use louwii\vescloginterpreter\models\DataType;
use louwii\vescloginterpreter\models\DataTypeCollection;

use yii\base\Component;

/**
 * DataCleaner Service
 *
 * @author    Louwii
 * @package   VescLogInterpreter
 * @since     1.0.0
 */
class DataCleaner extends Component
{
    public function reduceDataInCollectionToOnePerSecond(DataTypeCollection $dataTypeCollection)
    {
        foreach ($dataTypeCollection->getDataTypes() as $dataType) {
            if ($dataType->getName() == 'Time') {
                $this->reduceTimeDataTypeToOnePerSecond($dataType);
            } else {
                $this->reduceDataTypeToOnePerSecond($dataType);
            }
        }
    }

    /**
     * Process a DateType containing Time values to reduce all values to 1 per second
     * @param DataType $dataType
     */
    public function reduceTimeDataTypeToOnePerSecond(DataType $dataType)
    {
        $reducedValues = array();
        $previousTime = '';
        foreach ($dataType->getValues() as $value) {
            $timeParts = explode('.', $value); // Remove milliseconds
            if ($previousTime !== $timeParts[0]) {
                $reducedValues[] = $timeParts[0];
                $previousTime = $timeParts[0];
            }
        }

        $dataType->setValues($reducedValues);
    }

    public function reduceDataTypeToOnePerSecond(DataType $dataType)
    {
        $reducedValues = array();
        $tempSameSecondValues = array();
        $previousTime = '';
        foreach ($dataType->getValuesWithKey() as $time => $value) {
            $timeParts = explode('.', $time); // Remove milliseconds
            if ($previousTime == '') {
                $previousTime = $timeParts[0];
            }
            if ($previousTime == $timeParts[0]) {
                $tempSameSecondValues[] = $value;
            } else {
                // We're onto a new second
                
                if (count($tempSameSecondValues) > 0) {
                    $totalValues = 0;
                    foreach ($tempSameSecondValues as $tempValue) {
                        $totalValues += $tempValue;
                    }
                    $averageValue = (float)$totalValues / count($tempSameSecondValues);
                    $reducedValues[$previousTime] = round($averageValue, 2);
                    $tempSameSecondValues = array($value);
                }
                $previousTime = $timeParts[0];
            }
        }

        // Remaining values
        $totalValues = 0;
        foreach ($tempSameSecondValues as $tempValue) {
            $totalValues += $tempValue;
        }
        $averageValue = (float)$totalValues / count($tempSameSecondValues);
        $reducedValues[$previousTime] = round($averageValue, 2);

        $dataType->setValues($reducedValues);
    }

    public function reduceGeolocToOnePerSecond(array $geoloc)
    {
        $reducedGeoloc = array();
        $previousTime = '';
        $tempSameSecondValues = array();
        
        foreach ($geoloc as $geolocRow) {
            $timeParts = explode('.', $geolocRow['timeStr']); // Remove milliseconds
            if ($previousTime == '') {
                $previousTime = $timeParts[0];
            }

            if ($previousTime == $timeParts[0]) {
                $tempSameSecondValues[] = $geolocRow;
            } else {
                // We're onto a new second
                
                if (count($tempSameSecondValues) > 0) {
                    
                    $totalLat = 0;
                    $totalLon = 0;
                    foreach ($tempSameSecondValues as $tempValue) {
                        $totalLat += $tempValue['coordinates']['latitude'];
                        $totalLon += $tempValue['coordinates']['longitude'];
                    }

                    $averageLat = (float)$totalLat / count($tempSameSecondValues);
                    $averageLon = (float)$totalLon / count($tempSameSecondValues);
                    $reducedGeoloc[] = array('timeStr' => $previousTime, 'coordinates' => array('latitude' => $averageLat, 'longitude' => $averageLon));
                    $tempSameSecondValues = array($geolocRow);
                }
                $previousTime = $timeParts[0];
            }
        }

        // Remaining values
        $totalLat = 0;
        $totalLon = 0;
        foreach ($tempSameSecondValues as $tempValue) {
            $totalLat += $tempValue['coordinates']['latitude'];
            $totalLon += $tempValue['coordinates']['longitude'];
        }
        $averageLat = (float)$totalLat / count($tempSameSecondValues);
        $averageLon = (float)$totalLon / count($tempSameSecondValues);
        $reducedGeoloc[] = array('timeStr' => $previousTime, 'coordinates' => array('latitude' => $averageLat, 'longitude' => $averageLon));

        return $reducedGeoloc;
    }
}