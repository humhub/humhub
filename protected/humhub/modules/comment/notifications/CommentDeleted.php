<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\notifications;

use humhub\modules\comment\models\Comment;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\widgets\richtext\converter\RichTextToShortTextConverter;
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
        return Yii::t('CommentModule.notifications', 'Your comment \'{commentText}\' has been deleted by {displayName} for \'{reason}\'', [
            'displayName' => Html::tag('strong', Html::encode($this->originator->displayName)),
            'commentText' => $this->payload['commentText'],
            'reason' => $this->payload['reason'],
        ]);
    }

}
