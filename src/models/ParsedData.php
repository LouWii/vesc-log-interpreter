<?php
/**
 * VESC Log Interpreter plugin for Craft CMS 3.x
 *
 * Process log data from VESC Monitor to generate charts
 *
 * @link      http://github.com/louwii
 * @copyright Copyright (c) 2018 Louwii
 */

namespace louwii\vescloginterpreter\models;

use craft\base\Model;

/**
 * ParsedData Model
 *
 * @author    Louwii
 * @package   VescLogInterpreter
 * @since     1.0.0
 */
class ParsedData extends Model
{
    private $xAxisLabels;

    private $dataSets;

    private $parsingErrors;

    private $maxValues;

    private $minValues;

    private $averageValues;

    private $duration;

    private $geolocation;
    
    private $caching;

    public function __construct()
    {
        $this->parsingErrors = array();
    }

    public function getXAxisLabels()
    {
        return $this->xAxisLabels;
    }

    public function setXAxisLabels($xAxisLabels)
    {
        $this->xAxisLabels = $xAxisLabels;
    }

    public function getDataSets()
    {
        return $this->dataSets;
    }

    public function setDataSets($dataSets)
    {
        $this->dataSets = $dataSets;
    }

    public function getParsingErrors()
    {
        return $this->parsingErrors;
    }

    public function setParsingErrors(array $parsingErrors)
    {
        $this->parsingErrors = $parsingErrors;
    }

    public function getMaxValues()
    {
        return $this->maxValues;
    }

    public function setMaxValues($maxValues)
    {
        $this->maxValues = $maxValues;
    }

    public function getMinValues()
    {
        return $this->minValues;
    }

    public function setMinValues($minValues)
    {
        $this->minValues = $minValues;
    }

    public function getAverageValues()
    {
        return $this->averageValues;
    }

    public function setAverageValues($averageValues)
    {
        $this->averageValues = $averageValues;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function setDuration($duration)
    {
        return $this->duration = $duration;
    }

    public function getGeolocation()
    {
        return $this->geolocation;
    }

    public function setGeolocation($geolocation)
    {
        $this->geolocation = $geolocation;
    }

    public function getCaching()
    {
        return $this->caching;
    }

    public function setCaching($caching)
    {
        $this->caching = $caching;
    }
}