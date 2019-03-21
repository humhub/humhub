<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\security;

use Yii;
use yii\base\BaseObject;
use yii\base\Event;
use yii\helpers\Url;

/**
 * Events provides callbacks to handle events.
 *
 * @since 1.3
 * @author luke
 */
class Events extends BaseObject
{

    /**
     * @param $evt Event
     */
    public static function onAdvancedSettingsMenuInit($evt)
    {
        /* @var $menu \humhub\modules\admin\widgets\AdvancedSettingMenu */
        $menu = $evt->sender;
        $menu->addItem([
            'label' => Yii::t('SecurityModule.base', 'Security'),
            'url' => Url::toRoute(['/admin/setting/caching']),
            'icon' => '<i class="fa fa-dashboard"></i>',
            'sortOrder' => 100,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'security' && Yii::$app->controller->id == 'setting'),
            'isVisible' => Yii::$app->user->isAdmin(),
        ]);
    }

    public static function onBeforeAction($evt)
    {
        if(!Yii::$app->request->isAjax) {
            /**
             * https://developer.microsoft.com/en-us/microsoft-edge/platform/issues/13246371/
             */
            $policy = static::isEdge() ?  "script-src 'self' 'nonce-test'" :  "script-src 'nonce-test'";
            Yii::$app->response->headers->add('Content-Security-Policy', $policy);
        }
    }

    private static function isEdge()
    {
        return preg_match('/Edge/i',$_SERVER['HTTP_USER_AGENT']);
    }

}
