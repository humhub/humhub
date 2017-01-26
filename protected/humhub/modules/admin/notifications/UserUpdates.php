<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\notifications;

use Yii;
use humhub\modules\notification\components\BaseNotification;
use yii\bootstrap\Html;
use humhub\modules\user\models\User;
use humhub\modules\user\models\Group;

/**
 * UserUpdatesNotification
 *
 * Notifies about user updates
 *
 * @since 0.11
 */
class UserUpdates extends BaseNotification
{
    /**
     * @inheritdoc
     */
    public $moduleId = 'admin';

    public static $type = '';
    public static $to = '';

    /**
     * @inheritdoc
     */
    public function category()
    {
        return new AdminUserUpdatesCategory();
    }

    public static function sendToAdmins($about, $type)
    {
        $module = Yii::$app->getModule('notification');
        $mustNotify = $module->settings->get('notification.admin_user_updates_email');
        if ($mustNotify) {

            self::$type = $type;

            if(method_exists($about, 'getTarget')){
                self::$to = $about->getTarget()->email;
            }

            self::instance()
                ->from($about->user)
                ->about($about)
                ->sendBulk(
                    Group::getAdminGroup()->users
                );
        }
    }


    /**
     * @inheritdoc
     */
    public function html()
    {
        $this->viewName = "userupdates";

        return Yii::t('LikeModule.notification', "User {email_from} create {type} {email_to}", [
            'type' => UserUpdates::$type,
            'email_from' => $this->originator->email,
            'email_to' => isset($this->content) ? "to user ".$this->content->user->email : ((!empty(UserUpdates::$to)) ? "to user ".UserUpdates::$to : '')
        ]);
    }


}

?>
