<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/**
 * Created by PhpStorm.
 * User: buddha
 * Date: 19.09.2017
 * Time: 16:29
 */

namespace humhub\modules\mobile;


use Yii;
use yii\helpers\Url;

class Events extends \yii\base\Object
{
    /**
     * @param $event
     */
    public static function onAccountMenuInit($event)
    {
        $event->sender->addItem([
            'label' => Yii::t('MobileModule.base', 'Devices'),
            'id' => 'directory',
            'icon' => '<i class="fa fa-mobile-phone"></i>',
            'url' => Url::to(['/mobile/settings']),
            'sortOrder' => 500,
            'isActive' => Yii::$app->controller->module === 'mobile',
        ]);
    }
}