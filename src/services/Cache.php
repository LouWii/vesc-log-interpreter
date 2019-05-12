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
    public function getDatasetsCacheId($timestamp)
    {
        return VescLogInterpreter::getInstance()->name.'--datasets--'.$timestamp;
    }

    public function getAxisLabelsCacheId($timestamp)
    {
        return VescLogInterpreter::getInstance()->name.'--axis_labels--'.$timestamp;
    }

    public function getErrorsCacheId($timestamp)
    {
        return VescLogInterpreter::getInstance()->name.'--errors--'.$timestamp;
    }

    public function getMaxValuesCacheId($timestamp)
    {
        return VescLogInterpreter::getInstance()->name.'--maxValues--'.$timestamp;
    }

    /**
     * Cache processed data with a unique ID, to be able to keep it for later
     *
     * @param int $timestamp
     * @param array $datasets
     * @param array $errors
     * @return bool True if caching worked, false otherwise
     */
    public function cacheData($timestamp, $axisLabels, $datasets, $maxValues, $errors)
    {
        // Cache processed data
        // Use https://yii2-cookbook.readthedocs.io/caching/
        // Use timestamp to create unique IDs and avoid collision if people submits at the same time
        if ($datasets != NULL && count($datasets) > 0) {
            // If datasets exist, keep the data for a week
            $cacheTTL = 60*60*24*7;
        } else {
            // If no dataset, then it means process failed, no need to keep it for long
            $cacheTTL = 3600;
        }

        return
            Craft::$app->cache->set($this->getDatasetsCacheId($timestamp), $datasets, $cacheTTL)
            &&
            Craft::$app->cache->set($this->getAxisLabelsCacheId($timestamp), $axisLabels, $cacheTTL)
            &&
            Craft::$app->cache->set($this->getErrorsCacheId($timestamp), $errors, $cacheTTL)
            &&
            Craft::$app->cache->set($this->getMaxValuesCacheId($timestamp), $maxValues, $cacheTTL)
            ;
    }

    /**
     * Retrieve data from the cache
     *
     * @param [type] $timestamp
     * @return void
     */
    public function retrieveCachedData($timestamp)
    {
        $xAxisLabels = Craft::$app->cache->get($this->getAxisLabelsCacheId($timestamp));
        $datasets = Craft::$app->cache->get($this->getDatasetsCacheId($timestamp));
        $errors = Craft::$app->cache->get($this->getErrorsCacheId($timestamp));
        $maxValues = Craft::$app->cache->get($this->getMaxValuesCacheId($timestamp));

        return array(
            'axisLabels' => $xAxisLabels,
            'datasets' => $datasets,
            'errors' => $errors,
            'maxValues' => $maxValues,
        );
    }
}