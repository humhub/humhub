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
     * @return ContentContainerActiveRecord|null currently active container from app context.
     */
    public static function getCurrent()
    {
        $controller = Yii::$app->controller;
        if($controller instanceof ContentContainerController) {
            return $controller->contentContainer;
        }

        return null;
    }
}
