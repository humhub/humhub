<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ldap;

use humhub\components\Event;
use humhub\modules\ldap\models\LdapSettings;
use humhub\modules\user\authclient\Collection;
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
     * @param $event Event
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

    /**
     * @param $event Event
     */
    public static function onAuthClientCollectionSet($event)
    {
        if (LdapSettings::isEnabled()) {

            /** @var Collection $collection */
            $collection = $event->sender;

            $settings = new LdapSettings();
            $settings->loadSaved();
            $collection->setClient('ldap', $settings->getLdapAuthDefinition());
        }
    }

    /**
     * @param $event \yii\base\Event
     */
    public static function onConsoleApplicationInit($event)
    {
        $application = $event->sender;
        $application->controllerMap['ldap'] = commands\LdapController::class;
    }

}
