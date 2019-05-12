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
}