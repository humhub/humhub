<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\notifications;

use Yii;
use yii\bootstrap\Html;
use humhub\modules\notification\components\BaseNotification;
use yii\db\IntegrityException;

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
     * @throws IntegrityException
     */
    public function getUrl()
    {
        if ($this->originator === null) {
            throw new IntegrityException('Originator cannot be null.');
        }

        return $this->originator->getUrl();
    }

    /**
     * @inheritdoc
     */
    public function getMailSubject()
    {
        return $this->getInfoText($this->originator->displayName);
    }

    /**
     * @inheritdoc
     */
    public function html()
    {
        return $this->getInfoText(Html::tag('strong', Html::encode($this->originator->displayName)));
    }

    public function getInfoText($displayName)
    {
        return Yii::t('UserModule.notification', '{displayName} is now following you.', [
            'displayName' => $displayName,
        ]);
    }
}
