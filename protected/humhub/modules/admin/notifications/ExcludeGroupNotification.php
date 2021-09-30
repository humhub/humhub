<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\notifications;

use humhub\modules\notification\components\BaseNotification;
use humhub\modules\user\models\Group;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * It occurs when a member from the group is excluded
 *
 * @property Group $source
 * @since 1.3
 */
class ExcludeGroupNotification extends BaseNotification
{
    /**
     * @inheritdoc
     */
    public $moduleId = 'admin';

    /**
     * @inheritdoc
     */
    public function getUrl()
    {
        return Url::to(['/user/people']);
    }

    /**
     * @inheritdoc
     */
    public function category()
    {
        return new AdminNotificationCategory;
    }

    /**
     * @inheritdoc
     */
    public function getMailSubject()
    {
        return \Yii::t(
            'AdminModule.notification',
            'Notify from {appName}. You were removed from the group.',
            ['appName' => \Yii::$app->name]
        );
    }

    /**
     * @inheritdoc
     */
    public function html()
    {
        return \Yii::t(
            'AdminModule.notification',
            '{displayName} removed you from group {groupName}',
            [
                '{displayName}' => Html::tag('strong', Html::encode($this->originator->getDisplayName())),
                '{groupName}' => Html::tag('strong', Html::encode($this->source->name)),
            ]
        );
    }
}
