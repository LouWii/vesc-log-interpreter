<?php
/**
 * VESC Log Interpreter plugin for Craft CMS 3.x
 *
 * Process log data from VESC Monitor to generate charts
 *
 * @link      http://github.com/louwii
 * @copyright Copyright (c) 2018 Louwii
 */

namespace louwii\vescloginterpreter\variables;

use louwii\vescloginterpreter\models\ChartDataSet;
use louwii\vescloginterpreter\models\ParsedData;
use louwii\vescloginterpreter\VescLogInterpreter;

use Craft;

/**
 * VESC Log Interpreter Variable
 *
 * Craft allows plugins to provide their own template variables, accessible from
 * the {{ craft }} global variable (e.g. {{ craft.vescLogInterpreter }}).
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @author    Louwii
 * @package   VescLogInterpreter
 * @since     1.0.0
 */
class VescLogInterpreterVariable
{
    // Public Methods
    // =========================================================================

    /**
     * The action URL to use in the log file upload form
     * 
     * {{ craft.vescLogInterpreter.formActionUrl }}
     *
     * @return string
     */
    public function formActionUrl()
    {
        return '/actions/vesc-log-interpreter/process/vesc-log';
    }

    /**
     * {{ craft.vescLogInterpreter.vescLogDataFound }}
     */
    public function vescLogDataFound()
    {
        return $this->fetchFromCache('getXAxisLabels') !== null;
    }

    /**
     * Retrieve the vesc log label from cache
     * 
     * {{ craft.vescLogInterpreter.vescLogDataAxisLabels }}
     * 
     * Example usage: window.labels = {{ craft.vescLogInterpreter.vescLogDataAxisLabels|raw }}
     *
     * @return array
     */
    public function vescLogDataAxisLabels()
    {
        return $this->fetchFromCache('getXAxisLabels', true);
    }

    /**
     * Retrieve the vesc log data from cache
     * 
     * {{ craft.vescLogInterpreter.vescLogDataDatasets }}
     * 
     * Example usage: window.datasets = {{ craft.vescLogInterpreter.vescLogDataDatasets|raw }};
     *
     * @return string
     */
    public function vescLogDataDatasets()
    {
        $dataSets = $this->fetchFromCache('getDataSets');

        if ($dataSets === null) {
            return null;
        }

        // Need to convert all datasets arrays as they don't use int indexes, but strings
        // json_encode will transform those to Objects and we don't want that
        $returnArray = array();
        foreach ($dataSets as $datasetPart) {
            $returnArray[] = array_values($datasetPart);
        }
        return json_encode($returnArray);
    }

    /**
     * Retrieve the vesc log errors from cache
     * 
     * {{ craft.vescLogInterpreter.vescLogDataErrors }}
     *
     * @return array
     */
    public function vescLogDataErrors()
    {
        return $this->fetchFromCache('getParsingErrors');
    }

    /**
     * Retrieve an array containing all max values for the different types
     * 
     * {{ craft.vescLogInterpreter.vescLogDataMaxValues }}
     * or
     * {{ craft.vescLogInterpreter.vescLogDataMaxValues('MotorCurrent') }}
     * 
     * @return mixed
     */
    public function vescLogDataMaxValues($valueType = null)
    {
        $maxValues = $this->fetchFromCache('getMaxValues');

        if ($maxValues === null) {
            return null;
        }

        if ($valueType) {
            if (array_key_exists($valueType, $maxValues)) {
                return $maxValues[$valueType];
            }
            return null;
        }

        return $maxValues;
    }

    /**
     * Retrieve an array containing all min values for the different types
     * 
     * {{ craft.vescLogInterpreter.vescLogDataMinValues }}
     * or
     * {{ craft.vescLogInterpreter.vescLogDataMinValues('MotorCurrent') }}
     * 
     * @return mixed
     */
    public function vescLogDataMinValues($valueType = null)
    {
        $minValues = $this->fetchFromCache('getMinValues');

        if ($minValues === null) {
            return null;
        }

        if ($valueType) {
            if (array_key_exists($valueType, $minValues)) {
                return $minValues[$valueType];
            }
            return null;
        }

        return $minValues;
    }

    /**
     * {{ craft.vescLogInterpreter.vescLogDataAverageValues }}
     * or
     * {{ craft.vescLogInterpreter.vescLogDataAverageValues('MotorCurrent') }}
     * 
     * @return mixed
     */
    public function vescLogDataAverageValues($valueType = null)
    {
        $averageValues = $this->fetchFromCache('getAverageValues');

        if ($averageValues === null) {
            return null;
        }

        if ($valueType) {
            if (array_key_exists($valueType, $averageValues)) {
                return $averageValues[$valueType];
            }
            return null;
        }

        return $averageValues;
    }

    /**
     * {{ craft.vescLogInterpreter.vescLogDataDuration }}
     */
    public function vescLogDataDuration()
    {
        return $this->fetchFromCache('getDuration');
    }

    /**
     * {{ craft.vescLogInterpreter.vesLogDataGeolocation }}
     */
    public function vesLogDataGeolocation()
    {
        $geoloc = $this->fetchFromCache('getGeolocation');

        if ($geoloc === null || count($geoloc) == 0) {
            return null;
        }

        return $geoloc;
    }

    /**
     * {{ craft.vescLogInterpreter.vescLogCsvLabelJsonTranslations }}
     */
    public function vescLogCsvLabelJsonTranslations()
    {
        return json_encode(ChartDataSet::getCsvLabelsToEnglishLabel());
    }

    /**
     * Generic function that fetches parsed data from cache, and return result of $getter
     *  or null if something went wrong
     * @param string $getter
     * @param bool $jsonEncode
     */
    private function fetchFromCache(string $getter, $jsonEncode = false)
    {
        $timestamp = Craft::$app->request->get('log');
        if (!$timestamp) {
            return null;
        }

        $parsedData = VescLogInterpreter::getInstance()->cache->retrieveCachedData($timestamp);

        if (!$parsedData instanceof ParsedData || $parsedData->$getter() === null) {
            return null;
        }

        if ($parsedData->getCaching() == false) {
            VescLogInterpreter::getInstance()->cache->deleteCachedData($timestamp);
        }

        if ($jsonEncode) {
            return json_encode($parsedData->$getter());
        }

        return $parsedData->$getter();
    }
}
