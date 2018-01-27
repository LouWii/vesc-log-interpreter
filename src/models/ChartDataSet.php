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
    // public $backgroundColor;
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

    public $lineColors = array(
        'rgb(54, 162, 235)', //'blue'
        'rgb(75, 192, 192)', //'green'
        'rgb(201, 203, 207)', //'grey'
        'rgb(255, 159, 64)', //'orange'
        'rgb(153, 102, 255)', //'purple'
        'rgb(255, 99, 132)', //'red'
        'rgb(255, 205, 86)', //'yellow'
    );

    // Public Methods
    // =========================================================================

    public function __construct()
    {
        $this->label = '';
        $this->data = array();
        $this->fill = FALSE;
        // $this->backgroundColor = $this->lineColors[rand(0, (count($this->lineColors) - 1))];
        $this->borderColor = $this->lineColors[rand(0, (count($this->lineColors) - 1))];

        $this->pointRadius = 0;
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
}
