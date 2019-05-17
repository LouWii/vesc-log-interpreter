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
 * DataTypeCollection Model
 *
 * @author    Louwii
 * @package   VescLogInterpreter
 * @since     1.0.0
 */
class DataTypeCollection extends Model
{
    private $dataTypes;

    public function __construct()
    {
        $this->dataTypes = array();
    }

    public function addDataType(DataType $dataType)
    {
        if (!array_key_exists($dataType->getName(), $this->dataTypes)) {
            $this->dataTypes[$dataType->getName()] = $dataType;
        } else {
            // TODO: create proper exception
            throw new \Exception('DataType ' . $dataType->getName() . ' already exists in the collection.');
        }
    }

    public function getDataType(string $typeName)
    {
        if (array_key_exists($typeName, $this->dataTypes)) {
            return $this->dataTypes[$typeName];
        } else {
            throw new \Exception('DataType ' . $dataType->getName() . ' does not exist in the collection.');
        }
    }

    public function getDataTypes()
    {
        return $this->dataTypes;
    }

    /**
     * Add a value to a data type in the collection
     * If DataType doesn't exist, it''ll be created
     * @param string $typeName
     * @param mixed $value
     * @param mixed $valueKey A key to associate the value with
     */
    public function addValueToDataType(string $typeName, $value, $valueKey = null)
    {
        if (!array_key_exists($typeName, $this->dataTypes)) {
            $dataType = new DataType($typeName);
            $this->dataTypes[$typeName] = $dataType;
        }

        $this->dataTypes[$typeName]->addValue($value, $valueKey);
    }

    /**
     * Destructive function: will process all data for each type and keep only 1 value per second
     *  as the original data can sometime have multiple values per second
     */
    public function reduceAllDataTypeDataPerSecond($ignore = array('Time'))
    {
        foreach ($this->dataTypes as $dataType) {
            if ($dataType->getName() == 'Time') {
                $dataType->reduceTimeValues();
            } else {
                $dataType->reduceDataPerSecond();
            }
        }
    }

    /**
     * Get an array containing the max value for each type
     * 
     * @return array
     */
    public function getMaxValues()
    {
        $maxValues = array();

        foreach ($this->dataTypes as $dataType) {
            $maxValues[$dataType->getName()] = $dataType->getMaxValue();
        }

        return $maxValues;
    }

    /**
     * Get an array containing the min value for each type
     * 
     * @return array
     */
    public function getMinValues()
    {
        $minValues = array();

        foreach ($this->dataTypes as $dataType) {
            $minValues[$dataType->getName()] = $dataType->getMinValue();
        }

        return $minValues;
    }

    /**
     * Get an array containing the average value for each type
     */
    public function getAverageValues()
    {
        $averageValues = array();

        foreach ($this->dataTypes as $dataType) {
            $averageValues[$dataType->getName()] = $dataType->getAverageValue();
        }

        return $averageValues;
    }

    public function hasValues()
    {
        if (count($this->dataTypes)) {
            foreach ($this->dataTypes as $dataType) {
                if ($dataType->hasValues()) {
                    return true;
                }
            }
        }
        return false;
    }

    public function checkDataIntegrity()
    {
        $dataCount = -1;
        foreach ($this->dataTypes as $dataType) {
            if ($dataCount == -1) {
                $dataCount = count($dataType->getValues());
            } else {
                if ($dataCount != count($dataType->getValues())) {
                    throw new \Exception('Expected ' . $dataType->getName() . ' to have ' . $dataCount . ' values but got ' . count($dataType->getValues()) . ' instead');
                }
            }
        }
    }
}