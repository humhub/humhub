<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\content\helpers;

use humhub\modules\content\models\ContentContainer;
use Yii;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\components\ContentContainerController;

/**
 * Helper class for ContentContainer related problems.
 *
 * @since 1.3
 */
class ContentContainerHelper
{
    /**
     * @var ContentContainerActiveRecord container
     * @since 1.7
     */
    private static $container;

    private static $CACHE_LIMIT = 500;

    private static $cache = [];
    private static $guidCache = [];

    public static function flushCache()
    {
        static::$cache = [];
        static::$guidCache = [];
    }

    public static function getContainerByGuid(?string $guid)
    {
        if(!$guid) {
            return null;
        }

        if(isset(static::$guidCache[$guid])) {
            return static::$guidCache[$guid];
        }

        return static::addToCache(ContentContainer::findOne(['guid' => $guid]));
    }

    public static function getContainerByContentContainerId(?int $contentContainerId)
    {
        if($contentContainerId === null) {
            return null;
        }

        if(isset(static::$cache[$contentContainerId])) {
            return static::$cache[$contentContainerId];
        }

        return static::addToCache(ContentContainer::findOne(['id' => $contentContainerId]));
    }

    private static function addToCache(?ContentContainer $contentContainer) : ?ContentContainerActiveRecord
    {
        if(!$contentContainer) {
            return null;
        }

        $record = $contentContainer->getPolymorphicRelation();

        if($record && count(static::$cache) < static::$CACHE_LIMIT) {
            static::$cache[$contentContainer->id] = $record;
            static::$guidCache[$contentContainer->guid] = $record;
        }

        return $record;
    }

    /**
     * @param string|string[]|null| $type can be used to only search for a specific type of container
     * @return ContentContainerActiveRecord|null currently active container from app context.
     */
    public static function getCurrent($type = null) : ?ContentContainerActiveRecord
    {
        if(!static::$container) {
            $guid = Yii::$app->request->get('cguid', Yii::$app->request->get('sguid', Yii::$app->request->get('uguid')));
            static::$container = static::getContainerByGuid($guid);
        }

        if(!$type || !static::$container) {
            return static::$container;
        }

        if(is_string($type)) {
            $type = [$type];
        }

        foreach ($type as $allowedType) {
            if(is_a(static::$container, $allowedType)) {
                return static::$container;
            }
        }

        return null;
    }

    /**
     * Can be used to manually set the current container context.
     * @param ContentContainerActiveRecord|null $container
     * @since 1.7
     */
    public static function setCurrent(ContentContainerActiveRecord $container = null)
    {
        static::$container = $container;
    }
}
