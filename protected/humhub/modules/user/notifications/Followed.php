<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\notifications;

use Yii;
use yii\bootstrap\Html;
use humhub\modules\notification\components\BaseNotification;

/**
 * FollowNotification is fired to all users that are being
 * followed by other user
 */
class Followed extends BaseNotification
{

    /**
     * @inheritdoc
     */
    public $moduleId = 'user';

    /**
     * @inheritdoc
     */
    public $viewName = 'followed';

    /**
     * @inheritdoc
     */
    public function category()
    {
        return new FollowedNotificationCategory();
    }

    /**
     * @inheritdoc
     */
    public function getUrl()
    {
        return $this->originator->getUrl();
    }

    /**
     * @inheritdoc
     */
    public function getTitle(\humhub\modules\user\models\User $user)
    {
        return Yii::t('UserModule.notification', '{displayName} is now following you', [
                    'displayName' => Html::encode($this->originator->displayName),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function html()
    {
        return Yii::t('UserModule.notification', '{displayName} is now following you.', [
                    'displayName' => Html::tag('strong', Html::encode($this->originator->displayName)),
        ]);
    }

}

?>
