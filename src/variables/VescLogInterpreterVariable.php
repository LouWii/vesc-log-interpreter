<?php
/**
 * VESC Log Interpreter plugin for Craft CMS 3.x
 *
 * Process log data from VESC Monitor to generate charts
 *
 * @link      http://github.com/louwii
 * @copyright Copyright (c) 2018 Louwii
 */

namespace louwii\vescloginterpreter\variables;

use louwii\vescloginterpreter\VescLogInterpreter;

use Craft;

/**
 * VESC Log Interpreter Variable
 *
 * Craft allows plugins to provide their own template variables, accessible from
 * the {{ craft }} global variable (e.g. {{ craft.vescLogInterpreter }}).
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @author    Louwii
 * @package   VescLogInterpreter
 * @since     1.0.0
 */
class VescLogInterpreterVariable
{
    // Public Methods
    // =========================================================================

    /**
     * Whatever you want to output to a Twig template can go into a Variable method.
     * You can have as many variable functions as you want.  From any Twig template,
     * call it like this:
     *
     *     {{ craft.vescLogInterpreter.exampleVariable }}
     *
     * Or, if your variable requires parameters from Twig:
     *
     *     {{ craft.vescLogInterpreter.exampleVariable(twigValue) }}
     *
     * @param null $optional
     * @return string
     */
    public function exampleVariable($optional = null)
    {
        $result = "And away we go to the Twig template...";
        if ($optional) {
            $result = "I'm feeling optional today...";
        }
        return $result;
    }

    /**
     * The action URL to use in the log file upload form
     * {{ craft.vescLogInterpreter.formActionUrl() }}
     *
     * @return string
     */
    public function formActionUrl()
    {
        return '/actions/vesc-log-interpreter/process/vesc-log';
    }
}
