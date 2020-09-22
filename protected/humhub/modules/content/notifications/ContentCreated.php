<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\notifications;

use humhub\modules\notification\components\BaseNotification;
use Yii;
use yii\bootstrap\Html;
use humhub\modules\user\models\User;
use humhub\libs\Helpers;

/**
 * ContentCreatedNotification is fired to all users which are manually selected
 * in ContentFormWidget to receive a notification.
 */
class ContentCreated extends BaseNotification
{

    /**
     * @inheritdoc
     */
    public $viewName = 'contentCreated';

    /**
     * @inheritdoc
     */
    public $moduleId = 'content';

    /**
     * @inheritdoc
     */
    public function category()
    {
        return new \humhub\modules\content\notifications\ContentCreatedNotificationCategory();
    }

    /**
     * @inheritdoc
     */
    public function html()
    {
        if($this->source->content->container instanceof User && $this->record->user->is($this->source->content->container)) {
            return Yii::t('ContentModule.notifications', '{displayName} posted on your profile {contentTitle}.', [
                'displayName' => Html::tag('strong', Html::encode($this->originator->displayName)),
                'contentTitle' => $this->getContentInfo($this->source, false)
            ]);
        } else {
            return Yii::t('ContentModule.notifications', '{displayName} created {contentTitle}.', [
                'displayName' => Html::tag('strong', Html::encode($this->originator->displayName)),
                'contentTitle' => $this->getContentInfo($this->source)
            ]);
        }

    }

    /**
     * @inheritdoc
     */
    public function getMailSubject()
    {
        $user = $this->record->user;
        $contentInfo = $this->getContentPlainTextInfo();
        $space = $this->getSpace();
        if ($space) {
            if ($this->isExplicitNotifyUser($user)) {
                return Yii::t('ContentModule.notifications', '{originator} notifies you about {contentInfo} in {space}', ['originator' => Html::encode($this->originator->displayName),
                            'space' => Html::encode($space->displayName),
                            'contentInfo' => $contentInfo]);
            }
            return Yii::t('ContentModule.notifications', '{originator} just wrote {contentInfo} in space {space}', ['originator' => Html::encode($this->originator->displayName),
                        'space' => Html::encode($space->displayName),
                        'contentInfo' => $contentInfo]);
        } else {
            if ($this->isExplicitNotifyUser($user)) {
                return Yii::t('ContentModule.notifications', '{originator} notifies you about {contentInfo}', ['originator' => Html::encode($this->originator->displayName),
                            'contentInfo' => $contentInfo]);
            }
            return Yii::t('ContentModule.notifications', '{originator} just wrote {contentInfo}', ['originator' => Html::encode($this->originator->displayName),
                        'contentInfo' => $contentInfo]);
        }
    }

    protected function isExplicitNotifyUser(User $user)
    {
        $content = $this->getContent();
        foreach ($content->notifyUsersOfNewContent as $notifyUser) {
            if ($notifyUser->id === $user->id) {
                return true;
            }
        }
        return false;
    }

}

?>
