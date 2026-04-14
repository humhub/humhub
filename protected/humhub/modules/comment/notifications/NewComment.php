<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\notifications;

use humhub\helpers\Html;
use humhub\modules\comment\models\Comment;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\models\Notification;
use humhub\modules\user\models\User;
use humhub\modules\user\notifications\Mentioned;
use Yii;

/**
 * Notification for new comments
 *
 * @since 0.5
 */
class NewComment extends BaseNotification
{
    /**
     * @var Comment
     */
    public $source;

    /**
     * @inheritdoc
     */
    public $moduleId = 'comment';

    /**
     * @inheritdoc
     */
    public $viewName = 'newComment';

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
    public function send(User $user)
    {
        // Check if there is also a mention notification, so skip this notification
        if (Notification::find()->where([
            'class' => Mentioned::class,
            'user_id' => $user->id,
            'source_class' => $this->source::class,
            'source_pk' => $this->source->getPrimaryKey()])->count() > 0) {
            return;
        }

        parent::send($user);
    }

    /**
     * @inheritdoc
     */
    public function getGroupKey()
    {
        $model = $this->getCommentedRecord();

        if (!$model) {
            return null;
        }

        return $model::class . '-' . $model->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getMailSubject()
    {
        if ($this->groupCount > 1) {
            return $this->getGroupTitle();
        }

        $space = $this->getSpace();
        $user = $this->record->user;
        $contentRecord = $this->getCommentedRecord();

        if (!$contentRecord) {
            return '';
        }

        $contentInfo = $this->getContentInfo($contentRecord);

        if ($user->is($contentRecord->owner)) {
            if ($space) {
                return Yii::t('CommentModule.notification', "{displayName} just commented your {contentTitle} in space {space}", [
                    'displayName' => $this->originator->displayName,
                    'contentTitle' => $contentInfo,
                    'space' => $space->displayName,
                ]);
            }

            return Yii::t('CommentModule.notification', "{displayName} just commented your {contentTitle}", [
                'displayName' => $this->originator->displayName,
                'contentTitle' => $contentInfo,
            ]);
        }

        if ($space) {
            return Yii::t('CommentModule.notification', "{displayName} commented {contentTitle} in space {space}", [
                'displayName' => $this->originator->displayName,
                'contentTitle' => $contentInfo,
                'space' => $space->displayName,
            ]);
        }

        return Yii::t('CommentModule.notification', "{displayName} commented {contentTitle}", [
            'displayName' => $this->originator->displayName,
            'contentTitle' => $contentInfo,
        ]);
    }

    private function getGroupTitle()
    {
        $space = $this->getSpace();
        $user = $this->record->user;
        $contentRecord = $this->getCommentedRecord();

        if (!$contentRecord) {
            return '';
        }

        $contentInfo = $this->getContentInfo($contentRecord);

        if ($user->is($contentRecord->owner)) {
            if ($space) {
                return Yii::t('CommentModule.notification', "{displayNames} just commented your {contentTitle} in space {space}", [
                    'displayNames' => $this->getGroupUserDisplayNames(false),
                    'contentTitle' => $contentInfo,
                    'space' => $space->displayName,
                ]);
            }

            return Yii::t('CommentModule.notification', "{displayNames} just commented your {contentTitle}", [
                'displayNames' => $this->getGroupUserDisplayNames(false),
                'contentTitle' => $contentInfo,
            ]);
        }

        if ($space) {
            return Yii::t('CommentModule.notification', "{displayNames} commented {contentTitle} in space {space}", [
                'displayNames' => $this->getGroupUserDisplayNames(false),
                'contentTitle' => $contentInfo,
                'space' => $space->displayName,
            ]);
        }

        return Yii::t('CommentModule.notification', "{displayNames} commented {contentTitle}", [
            'displayNames' => $this->getGroupUserDisplayNames(false),
            'contentTitle' => $contentInfo,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function html()
    {
        $contentInfo = $this->getContentInfo($this->getCommentedRecord(), true);

        if (!$contentInfo) {
            $contentInfo = Yii::t('CommentModule.notification', "[Deleted]");
        }

        if ($this->groupCount > 1) {
            return Yii::t('CommentModule.notification', "{displayNames} commented {contentTitle}.", [
                'displayNames' => $this->getGroupUserDisplayNames(),
                'contentTitle' => $contentInfo,
            ]);
        }
        return Yii::t('CommentModule.notification', "{displayName} commented {contentTitle}.", [
            'displayName' => Html::tag('strong', Html::encode($this->originator->displayName)),
            'contentTitle' => $contentInfo,
        ]);
    }

    /**
     * The commented record e.g. a Post
     *
     * @return ContentActiveRecord
     */
    public function getCommentedRecord()
    {
        return $this->source->content->getPolymorphicRelation();
    }


    /**
     * @inheritdoc
     */
    public function getUrl()
    {
        return $this->source->url;
    }
}
