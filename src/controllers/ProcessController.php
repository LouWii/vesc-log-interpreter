<?php
/**
 * VESC Log Interpreter plugin for Craft CMS 3.x
 *
 * Process log data from VESC Monitor to generate charts
 *
 * @link      http://github.com/louwii
 * @copyright Copyright (c) 2018 Louwii
 */

namespace louwii\vescloginterpreter\controllers;

use louwii\vescloginterpreter\VescLogInterpreter;

use Craft;
use craft\web\Controller;

/**
 * Process Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    Louwii
 * @package   VescLogInterpreter
 * @since     1.0.0
 */
class ProcessController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['index', 'vesc-log'];

    /**
     * Only accept certain types of file
     *
     * @var array
     */
    protected $validExtensions = array('csv', 'txt');

    // Public Methods
    // =========================================================================

    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/vesc-log-interpreter/process
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $result = 'Welcome to the ProcessController actionIndex() method';

        return $result;
    }

    /**
     * Handle POST request with Vesc log file
     * Action URL: actions/vesc-log-interpreter/process/vesc-log
     *
     * @return mixed
     */
    public function actionVescLog()
    {
        // Check https://gist.github.com/sathoro/8178981 for inspiration
        $result = 'Welcome to the ProcessController actionDoSomething() method';

        $errors = array();

        if (array_key_exists('vescLogFile', $_FILES))
        {
            $uploadedFile = $_FILES['vescLogFile'];
            $fileError = $uploadedFile['error'];
            if (!$fileError)
            {
                $filename = $uploadedFile['name'];
                $fileAbsolutePath = $uploadedFile['tmp_name'];
                $filenameParts = explode('.', $filename);
                $extension = end($filenameParts);

                if (!in_array($extension, $this->validExtensions))
                {
                    $errors[] = "$filename has an invalid extension.";
                }

                echo $fileAbsolutePath;
            }
        }
        else
        {
            $errors[] = 'Could not find file.';
        }

        // Put all our data in cache so it can be displayed after the redirect


        // Redirect to provided page in POST


        return $result;
    }
}
