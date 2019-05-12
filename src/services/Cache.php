<?php
/**
 * VESC Log Interpreter plugin for Craft CMS 3.x
 *
 * Process log data from VESC Monitor to generate charts
 *
 * @link      http://github.com/louwii
 * @copyright Copyright (c) 2018 Louwii
 */

namespace louwii\vescloginterpreter\services;

use louwii\vescloginterpreter\VescLogInterpreter;
use louwii\vescloginterpreter\models\ParsedData;

use Craft;
use yii\base\Component;

/**
 * Cache Service
 *
 * @author    Louwii
 * @package   VescLogInterpreter
 * @since     1.0.0
 */
class Cache extends Component
{
    public function getParsedDataCacheId($timestamp)
    {
        return VescLogInterpreter::getInstance()->name.'--parsed--'.$timestamp;
    }

    /**
     * Cache processed data with a unique ID, to be able to keep it for later
     *
     * @param int $timestamp
     * @param ParsedData $parsedData
     * @return bool True if caching worked, false otherwise
     */
    public function cacheData($timestamp, ParsedData $parsedData)
    {
        // Cache processed data
        // Use https://yii2-cookbook.readthedocs.io/caching/
        // Use timestamp to create unique IDs and avoid collision if people submits at the same time
        if ($parsedData != NULL && count($parsedData->getDataSets()) > 0) {
            // If datasets exist, keep the data for a week
            $cacheTTL = 60*60*24*7;
        } else {
            // If no dataset, then it means process failed, no need to keep it for long
            $cacheTTL = 3600;
        }

        return Craft::$app->cache->set($this->getParsedDataCacheId($timestamp), $parsedData, $cacheTTL);
    }

    /**
     * Retrieve data from the cache
     *
     * @param $timestamp
     * @return void
     */
    public function retrieveCachedData($timestamp)
    {
        $parsedData = Craft::$app->cache->get($this->getParsedDataCacheId($timestamp));

        return $parsedData;
    }
}