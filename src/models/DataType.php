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

    public function setValues($values)
    {
        $this->values = $values;
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
}