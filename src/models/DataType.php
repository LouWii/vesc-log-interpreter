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
 * DataType Model
 * 
 * A data type stores all the values for a specific type of data (MotorCurrent or Speed or Distance...)
 *
 * @author    Louwii
 * @package   VescLogInterpreter
 * @since     1.0.0
 */
class DataType extends Model
{
    private $name;

    private $values;

    private $cachedMax = null;
    private $cachedMin = null;
    private $cachedAverage = null;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->values = array();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function addValue($value, $key = null)
    {
        if ($key == null) {
            $this->values[] = $value;
        } else {
            $this->values[$key] = $value;
        }
        $this->resetCache();
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return array_values($this->values);
    }

    public function getValuesWithKey()
    {
        return $this->values;
    }

    public function hasValues()
    {
        return count($this->values) > 0;
    }

    /**
     * Get the maximum value
     */
    public function getMaxValue()
    {
        if ($this->cachedMax == null) {
            $this->calculateMinMaxAverage();
        }
        return $this->cachedMax;
    }

    /**
     * Get the minimum value
     */
    public function getMinValue()
    {
        if ($this->cachedMin == null) {
            $this->calculateMinMaxAverage();
        }
        return $this->cachedMin;
    }

    /**
     * Get the average value
     */
    public function getAverageValue()
    {
        if ($this->cachedAverage == null) {
            $this->calculateMinMaxAverage();
        }
        return $this->cachedAverage;
    }

    private function calculateMinMaxAverage()
    {
        $minValue = PHP_INT_MAX;
        $maxValue = PHP_INT_MIN;
        $total = 0.0;
        foreach ($this->values as $value) {
            if ($value < $minValue) {
                $minValue = $value;
            }
            if ($value > $maxValue) {
                $maxValue = $value;
            }
            if (is_int($value) || is_float($value) || is_double($value)) {
                $total += $value;
            }
        }

        $this->cachedMax = $maxValue;
        $this->cachedMin = $minValue;
        if (count($this->values) > 0) {
            $this->cachedAverage = round((float)$total/count($this->values), 2);
        }
    }

    private function resetCache()
    {
        $this->cachedMin = null;
        $this->cachedMax = null;
        $this->cachedAverage = null;
    }

    /**
     * Destructive function: will process all data for each type and keep only 1 value per second
     * Note: The key of the value must be a data/time string otherwise this won't work!
     */
    public function reduceDataPerSecond()
    {
        $reducedValues = array();
        $tempSameSecondValues = array();
        $previousTime = '';
        foreach ($this->values as $time => $value) {
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

        $this->values = $reducedValues;
    }

    /**
     * Destructive function: will process values as date time string to keep only 1 value per second
     */
    public function reduceTimeValues()
    {
        $reducedValues = array();
        $previousTime = '';
        foreach ($this->values as $value) {
            $timeParts = explode('.', $value); // Remove milliseconds
            if ($previousTime !== $timeParts[0]) {
                $reducedValues[] = $timeParts[0];
                $previousTime = $timeParts[0];
            }
        }

        $this->values = $reducedValues;
    }
}