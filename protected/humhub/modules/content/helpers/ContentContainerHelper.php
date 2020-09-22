<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\content\helpers;

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
     * @param string|null $type type filter available since 1.4
     * @return ContentContainerActiveRecord|null currently active container from app context.
     */
    public static function getCurrent($type = null)
    {
        $controller = Yii::$app->controller;
        if($controller instanceof ContentContainerController) {
            return (!$type || get_class($controller->contentContainer) === $type) ? $controller->contentContainer : null;
        }

        return null;
    }
}
