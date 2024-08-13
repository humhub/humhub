<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\stream\helpers;


use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use yii\helpers\Url;

class StreamHelper
{
    /**
     * @param ContentContainerActiveRecord $container
     * @param array $options
     * @since 1.3
     */
    public static function createUrl(ContentContainerActiveRecord $container, $options = []) {
        if($container instanceof Space) {
            return $container->createUrl('/space/space/home', $options);
        } elseif($container instanceof User) {
            return $container->createUrl('/user/profile/home', $options);
        }
    }
}
