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

    public function addValue($value)
    {
        $this->values[] = $value;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }
}