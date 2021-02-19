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

    public static function getContainerByContentContainerId(?int $contentContainerId)
    {
        $result = null;

        if($contentContainerId === null) {
            return $result;
        }

        if(isset(static::$cache[$contentContainerId])) {
            return static::$cache[$contentContainerId];
        }

        $contentContainer = ContentContainer::findOne(['id' => $contentContainerId]);

        if($contentContainer && count(static::$cache) < static::$CACHE_LIMIT) {
            $result = $contentContainer->getPolymorphicRelation();
            static::$cache[$contentContainerId] = $result;
        }

        return $result;
    }

    /**
     * @param string|null $type type filter available since 1.4
     * @return ContentContainerActiveRecord|null currently active container from app context.
     */
    public static function getCurrent($type = null)
    {
        if(!static::$container) {
            $controller = Yii::$app->controller;
            if($controller instanceof ContentContainerController) {
                static::$container =  $controller->contentContainer;
            }
        }

        if(static::$container && $type && !is_a(static::$container,  $type)) {
            return null;
        }

        return static::$container;
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
