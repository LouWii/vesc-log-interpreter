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
     */
    public function addValueToDataType(string $typeName, $value)
    {
        if (!array_key_exists($typeName, $this->dataTypes)) {
            $dataType = new DataType($typeName);
            $this->dataTypes[$typeName] = $dataType;
        }

        $this->dataTypes[$typeName]->addValue($value);
    }

    public function getMaxValues()
    {
        $maxValues = array();

        foreach ($this->dataTypes as $dataType) {
            $maxValues[$dataType->getName()] = $dataType->getMaxValue();
        }

        return $maxValues;
    }
}