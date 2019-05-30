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

use louwii\vescloginterpreter\VescLogInterpreter;

use Craft;
use craft\base\Model;

/**
 * ChartDataSet Model
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    Louwii
 * @package   VescLogInterpreter
 * @since     1.0.0
 */
class ChartDataSet extends Model
{
    // Public Properties
    // =========================================================================

    public $label;
    public $data;
    public $backgroundColor;
    public $borderColor;
    public $fill;

    // Points settings
    // public $pointBackgroundColor;
    // public $pointBorderColor;
    // public $pointBorderWidth;
    public $pointRadius;
    // public $pointStyle;
    // public $pointHitRadius;
    // public $pointHoverBackgroundColor;
    // public $pointHoverBorderColor;
    // public $pointHoverBorderWidth;
    // public $pointHoverRadius;

    public static $lineColors = array(
        'blue'         => 'rgb(54, 162, 235)',
        'lightBlue'    => 'rgb(25, 206, 234)',
        'paleBlue'     => 'rgb(92, 159, 224)',
        'marine'       => 'rgb(32, 37, 79)',
        'green'        => 'rgb(75, 192, 192)',
        'darkGreen'    => 'rgb(23, 81, 16)',
        'grey'         => 'rgb(201, 203, 207)',
        'lightOrange'  => 'rgb(255, 159, 64)',
        'brightOrange' => 'rgb(234, 93, 0)',
        'lightPurple'  => 'rgb(153, 102, 255)',
        'purple'       => 'rgb(96, 60, 168)',
        'pink'         => 'rgb(255, 99, 132)',
        'brightRed'    => 'rgb(226, 6, 6)',
        'darkRed'      => 'rgb(132, 41, 41)',
        'yellow'       => 'rgb(255, 205, 86)',
    );

    public static $typeToColor = array(
        'TempPcb' => 'lightOrange',
        'MotorCurrent' => 'darkRed',
        'BatteryCurrent' => 'marine',
        'DutyCycle' => 'brightOrange',
        'Speed' => 'pink',
        'InpVoltage' => 'paleBlue',
        'AmpHours' => 'darkGreen',
        'AmpHoursCharged' => 'green',
        'WattHours' => 'yellow',
        'WattHoursCharged' => 'blue',
        'Distance' => 'purple',
        'Power' => 'lightBlue',
        'Fault' => 'brightRed',
        'Altitude' => 'grey',
        'GPSSpeed' => 'lightPurple',
    );

    public static $csvLabelToEnglishLabel = array(
        'TempPcb'        => array(
            'label' => 'PCB Temp',
            'unit' => '°C'
        ),
        'MotorCurrent'   => array(
            'label' => 'Motor Current',
            'unit' => 'A'
        ),
        'BatteryCurrent' => array(
            'label' => 'Battery Current',
            'unit' => 'A'
        ),
        'DutyCycle'      => array(
            'label' => 'Duty',
            'unit' => '%'
        ),
        'Speed'          => array(
            'label' => 'Speed',
            'unit' => 'km/h'
        ),
        'InpVoltage'     => array(
            'label' => 'Battery Voltage',
            'unit' => 'V'
        ),
        'AmpHours'       => array(
            'label' => 'Ah',
            'unit' => 'Ah'
        ),
        'AmpHoursCharged' => array(
            'label' => 'Ah Charged',
            'unit' => 'Ah'
        ),
        'WattHours'      => array(
            'label' => 'Wh',
            'unit' => 'Wh'
        ),
        'WattHoursCharged' => array(
            'label' => 'Wh Charged',
            'unit' => 'Wh'
        ),
        'Distance'       => array(
            'label' => 'Distance',
            'unit' => 'km'
        ),
        'Power'          => array(
            'label' => 'Power',
            'unit' => 'W'
        ),
        'Fault'          => array(
            'label' => 'Vesc Fault',
            'unit' => ''
        ),
        'Altitude'       => array(
            'label' => 'Altitude',
            'unit' => 'm'
        ),
        'GPSSpeed'       => array(
            'label' => 'GPS Speed',
            'unit' => 'km/h'
        ),
    );

    // Public Methods
    // =========================================================================

    public function __construct()
    {
        $this->label = '';
        $this->data = array();
        $this->fill = FALSE;
        // $this->backgroundColor = self::$lineColors[rand(0, (count(self::$lineColors) - 1))];
        $randIdx = rand(0, (count(self::$lineColors) - 1));
        $this->borderColor = array_values(self::$lineColors)[$randIdx];

        $this->pointRadius = 0;
    }

    public function setLabel(string $label)
    {
        $this->label = $label;

        // Set color depending on the label name
        if (array_key_exists($label, self::$typeToColor)) {
            $labelColor = self::$typeToColor[$label];
            if (array_key_exists($labelColor, self::$lineColors)) {
                $this->borderColor = self::$lineColors[$labelColor];
                $this->fill = self::$lineColors[$labelColor];
                $this->backgroundColor = self::$lineColors[$labelColor];
            }
        }
    }

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules()
    {
        return [
            // ['someAttribute', 'string'],
            // ['someAttribute', 'default', 'value' => 'Some Default'],
            ['label', 'string'],
            ['fill', 'boolean'],
            ['borderColor', 'string']
        ];
    }

    /**
     * Get "nice" english label from a CSV label
     */
    public static function getEnglishLabelFor(string $csvLabel)
    {
        if (array_key_exists($csvLabel, $this->csvLabelToEnglishLabel)) {
            return $this->csvLabelToEnglishLabel[$csvLabel];
        }
        return $csvLabel;
    }

    public static function getCsvLabelsToEnglishLabel()
    {
        // Populate colors from color array
        $tempArray = ChartDataSet::$csvLabelToEnglishLabel;
        foreach ($tempArray as $labelKey => $labelData) {
            $colorName = ChartDataSet::$typeToColor[$labelKey];
            $labelData['color'] = self::$lineColors[$colorName];
            $tempArray[$labelKey] = $labelData;
        }
        return $tempArray;
    }
}
