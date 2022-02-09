<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\notifications;

use humhub\modules\comment\models\Comment;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\models\Notification;
use humhub\modules\user\models\User;
use humhub\modules\user\notifications\Mentioned;
use Yii;
use yii\bootstrap\Html;
use yii\helpers\Json;

/**
 * CommentDeletedNotification is fired when admin deletes a comment
 */
class CommentDeleted extends BaseNotification
{
    /**
     * @inheritdoc
     */
    public $requireSource = false;

    /**
     * @inheritdoc
     */
    public $moduleId = 'comment';

    /**
     * @inheritdoc
     */
    public function category()
    {
        return new CommentNotificationCategory();
    }

    /**
     * @inheritdoc
     */
    public function html()
    {
        $this->payload = Json::decode($this->record->payload);

        return Yii::t('CommentModule.notifications', 'Your comment under the {contentTitle} was deleted by {displayName}. Reason: {message}', [
            'displayName' => Html::tag('strong', Html::encode($this->originator->displayName)),
            'contentTitle' => $this->payload['contentTitle'],
            'message' => $this->payload['message'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function about($source)
    {
        if (!$source) {
            return $this;
        }

        $this->payload['contentTitle'] = $this->getContentPlainTextInfo($source->getCommentedRecord());

        return $this->payload();
    }


    /**
     * Set a `message` property
     */
    public function commented($message)
    {
        if (!$message) {
            return $this;
        }

        $this->payload['message'] = $message;

        return $this->payload();
    }
}
