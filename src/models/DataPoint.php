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
 * DataPoint Model
 * 
 * A data point corresponds to a line from a CSV.
 * It relates a DateTime to different values for that point in time (MotorCurrent, Speed...)
 *
 * @author    Louwii
 * @package   VescLogInterpreter
 * @since     1.0.0
 */
class DataPoint extends Model
{
    private $dateTime;

    private $values;

    public function getDateTime()
    {
        return $this->dateTime;
    }

    public function setDateTime($dateTime)
    {
        if (is_string($dateTime)) {
            $this->dateTime = new \DateTime($dateTime);
        } elseif ($dateTime instanceof \DateTime) {
            $this->dateTime = $dateTime;
        } else {
            throw new \UnexpectedValueException('$dateTime must be a string type or DateTime');
        }
    }

    public function addValue(string $name, $value)
    {
        $this->values[$name] = $value;
    }

    public function removeValue(string $name)
    {
        if (array_key_exists($name, $this->values)) {
            unset($this->values[$name]);
        }
    }

    public function getValues()
    {
        return $this->values;
    }

    public function __toString()
    {
        return $this->dateTime->format('Y-m-d H:i:s') . ' ('.')';
    }
}