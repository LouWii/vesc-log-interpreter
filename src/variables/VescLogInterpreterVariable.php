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
    private $cachedParsedData;

    // Public Methods
    // =========================================================================

    /**
     * Whatever you want to output to a Twig template can go into a Variable method.
     * You can have as many variable functions as you want.  From any Twig template,
     * call it like this:
     *
     *     {{ craft.vescLogInterpreter.exampleVariable }}
     *
     * Or, if your variable requires parameters from Twig:
     *
     *     {{ craft.vescLogInterpreter.exampleVariable(twigValue) }}
     *
     * @param null $optional
     * @return string
     */
    public function exampleVariable($optional = null)
    {
        $result = "And away we go to the Twig template...";
        if ($optional) {
            $result = "I'm feeling optional today...";
        }
        return $result;
    }

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
        $timestamp = Craft::$app->request->get('log');
        if (!$timestamp) {
            return false;
        }

        $parsedData = VescLogInterpreter::getInstance()->cache->retrieveCachedData($timestamp);

        if (!$parsedData instanceof ParsedData || $parsedData->getXAxisLabels() === NULL) {
            return false;
        }

        return true;
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
        $timestamp = Craft::$app->request->get('log');
        if (!$timestamp) {
            return null;
        }

        $parsedData = VescLogInterpreter::getInstance()->cache->retrieveCachedData($timestamp);

        if (!$parsedData instanceof ParsedData || $parsedData->getXAxisLabels() === NULL) {
            return null;
        }

        return json_encode($parsedData->getXAxisLabels());
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
        $timestamp = Craft::$app->request->get('log');
        if (!$timestamp) {
            return null;
        }

        $parsedData = VescLogInterpreter::getInstance()->cache->retrieveCachedData($timestamp);

        if (!$parsedData instanceof ParsedData || $parsedData->getXAxisLabels() === NULL) {
            return null;
        }

        // Need to convert all datasets arrays as they don't use int indexes, but strings
        // json_encode will transform those to Objects and we don't want that
        $returnArray = array();
        foreach ($parsedData->getDataSets() as $datasetPart) {
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
        $timestamp = Craft::$app->request->get('log');
        if (!$timestamp) {
            return null;
        }

        $parsedData = VescLogInterpreter::getInstance()->cache->retrieveCachedData($timestamp);

        if (!$parsedData instanceof ParsedData || $parsedData->getXAxisLabels() === NULL) {
            return null;
        }

        return $parsedData->getParsingErrors();
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
        $timestamp = Craft::$app->request->get('log');
        if (!$timestamp) {
            return null;
        }

        $parsedData = VescLogInterpreter::getInstance()->cache->retrieveCachedData($timestamp);

        if (!$parsedData instanceof ParsedData || $parsedData->getMaxValues() === NULL) {
            return null;
        }

        if ($valueType) {
            if (array_key_exists($valueType, $parsedData->getMaxValues())) {
                return $parsedData->getMaxValues()[$valueType];
            }
            return null;
        }

        return $parsedData->getMaxValues();
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
        $timestamp = Craft::$app->request->get('log');
        if (!$timestamp) {
            return null;
        }

        $parsedData = VescLogInterpreter::getInstance()->cache->retrieveCachedData($timestamp);

        if (!$parsedData instanceof ParsedData || $parsedData->getMinValues() === NULL) {
            return null;
        }

        if ($valueType) {
            if (array_key_exists($valueType, $parsedData->getMinValues())) {
                return $parsedData->getMinValues()[$valueType];
            }
            return null;
        }

        return $parsedData->getMinValues();
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
        $timestamp = Craft::$app->request->get('log');
        if (!$timestamp) {
            return null;
        }

        $parsedData = VescLogInterpreter::getInstance()->cache->retrieveCachedData($timestamp);

        if (!$parsedData instanceof ParsedData || $parsedData->getAverageValues() === NULL) {
            return null;
        }

        if ($valueType) {
            if (array_key_exists($valueType, $parsedData->getAverageValues())) {
                return $parsedData->getAverageValues()[$valueType];
            }
            return null;
        }

        return $parsedData->getAverageValues();
    }

    /**
     * {{ craft.vescLogInterpreter.vescLogDataDuration }}
     */
    public function vescLogDataDuration()
    {
        $timestamp = Craft::$app->request->get('log');
        if (!$timestamp) {
            return null;
        }

        $parsedData = VescLogInterpreter::getInstance()->cache->retrieveCachedData($timestamp);

        if (!$parsedData instanceof ParsedData || $parsedData->getDuration() === NULL) {
            return null;
        }

        return $parsedData->getDuration();
    }
}
