<?php

/**
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\components;

use Yii;
use yii\db\ActiveRecord;

/**
 * BaseSetting
 *
 * @since  1.13.2
 * @author Martin Rüegg <martin.rueegg@metaworx.ch>
 */
abstract class SettingActiveRecord extends ActiveRecord
{
    /**
     * @const array List of fields to be used to generate the cache key
     */
    protected const CACHE_KEY_FIELDS = ['module_id'];

    /**
     * @const string Used as the formatting pattern for sprintf when generating the cache key
     */
    protected const CACHE_KEY_FORMAT = 'settings-%s';

    public static function deleteAll($condition = null, $params = [])
    {
        // get a grouped list of cache entries that are going to be deleted, grouped by static::CACHE_KEY_FIELDS
        $containers = self::find()
            ->where($condition, $params)
            ->groupBy(static::CACHE_KEY_FIELDS)
            ->select(static::CACHE_KEY_FIELDS)
            ->all();

        // going through that list, deleting the respective cache
        array_walk($containers, static function (ActiveRecord $rec) {
            $key = static::getCacheKey(...array_values($rec->toArray()));
            Yii::$app->cache->delete($key);
        });

        // proceed to delete the records from the database
        return parent::deleteAll($condition, $params);
    }

    /**
     * @param string $moduleId  Name of the module to create the cache key for
     * @param mixed  ...$values Additional arguments, if required by the static::CACHE_KEY_FORMAT
     *
     * @return string The key used for cache operation
     */
    public static function getCacheKey(string $moduleId, ...$values): string
    {
        return sprintf(static::CACHE_KEY_FORMAT, $moduleId, ...$values);
    }
}
