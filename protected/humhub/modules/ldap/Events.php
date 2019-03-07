<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ldap;

use Yii;
use yii\base\BaseObject;
use yii\helpers\Url;

/**
 * Events provides callbacks for all defined module events.
 *
 * @author luke
 */
class Events extends BaseObject
{
    /**
     * @param $event
     */
    public static function onAuthenticationMenu($event)
    {
        $event->sender->addItem([
            'label' => Yii::t('LdapModule.base', 'LDAP'),
            'url' => Url::to(['/ldap/admin']),
            'sortOrder' => 200,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'ldap' && Yii::$app->controller->id == 'admin'),
        ]);
    }
}
