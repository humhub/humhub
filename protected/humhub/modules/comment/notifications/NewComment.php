<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\notifications;

use Yii;
use yii\bootstrap\Html;
use humhub\modules\user\models\User;
use humhub\libs\Helpers;

/**
 * Notification for new comments
 *
 * @since 0.5
 */
class NewComment extends \humhub\modules\notification\components\BaseNotification
{

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
        if (\humhub\modules\notification\models\Notification::find()->where([
                    'class' => \humhub\modules\user\notifications\Mentioned::className(),
                    'user_id' => $user->id,
                    'source_class' => $this->source->className(),
                    'source_pk' => $this->source->getPrimaryKey()])->count() > 0) {
            return;
        }

        return parent::send($user);
    }

    /**
     * @inheritdoc
     */
    public function getGroupKey()
    {
        $model = $this->getCommentedRecord();
        return $model->className() . '-' . $model->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getTitle(User $user)
    {
        if ($this->groupCount > 1) {
            return $this->getGroupTitle($user);
        }
        
        $contentRecord = $this->getCommentedRecord();
        $space = $this->getSpace();

        if ($user->is($contentRecord->owner)) {
            if ($space) {
                return Yii::t('CommentModule.notification', "{displayName} just commented your content \"{preview}\" in space {space}", [
                            'displayName' =>  Html::encode($this->originator->displayName),
                            'preview' => Helpers::truncateText($contentRecord->getContentDescription(), 25),
                            'space' => Html::encode($space->displayName)
                ]);
            }
            return Yii::t('CommentModule.notification', "{displayName} just commented your content \"{preview}\"", [
                        'displayName' =>  Html::encode($this->originator->displayName),
                        'preview' => Helpers::truncateText($contentRecord->getContentDescription(), 25),
            ]);
        } else if ($space) {
            return Yii::t('CommentModule.notification', "{displayName} commented \"{preview}\" in space {space}", [
                        'displayName' =>  Html::encode($this->originator->displayName),
                        'preview' => Helpers::truncateText($contentRecord->getContentDescription(), 25),
                        'space' => Html::encode($space->displayName)
            ]);
        } else {
            return Yii::t('CommentModule.notification', "{displayName} commented \"{preview}\"", [
                        'displayName' =>  Html::encode($this->originator->displayName),
                        'preview' => Helpers::truncateText($contentRecord->getContentDescription(), 25),
            ]);
        }
    }

    private function getGroupTitle(User $user)
    {
        $contentRecord = $this->getCommentedRecord();
        $space = $this->getSpace();
        
        if ($user->is($contentRecord->owner)) {
            if ($space) {
                return Yii::t('CommentModule.notification', "{displayNames} just commented your content \"{preview}\" in space {space}", [
                            'displayNames' => $this->getGroupUserDisplayNames(),
                            'preview' => Helpers::truncateText($contentRecord->getContentDescription(), 25),
                            'space' => Html::encode($space->displayName)
                ]);
            }
            return Yii::t('CommentModule.notification', "{displayNames} just commented your content \"{preview}\"", [
                        'displayNames' => $this->getGroupUserDisplayNames(),
                        'preview' => Helpers::truncateText($contentRecord->getContentDescription(), 25),
            ]);
        } else if ($space) {
            return Yii::t('CommentModule.notification', "{displayNames} commented \"{preview}\" in space {space}", [
                        'displayNames' => $this->getGroupUserDisplayNames(),
                        'preview' => Helpers::truncateText($contentRecord->getContentDescription(), 25),
                        'space' => Html::encode($space->displayName)
            ]);
        } else {
            return Yii::t('CommentModule.notification', "{displayNames} commented \"{preview}\"", [
                        'displayNames' => $this->getGroupUserDisplayNames(),
                        'preview' => Helpers::truncateText($contentRecord->getContentDescription(), 25),
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    public function html()
    {
        $contentInfo = $this->getContentInfo($this->getCommentedRecord());

        if ($this->groupCount > 1) {
            return Yii::t('CommentModule.notification', "{displayNames} commented {contentTitle}.", [
                        'displayNames' => $this->getGroupUserDisplayNames(),
                        'contentTitle' => $contentInfo
            ]);
        }
        return Yii::t('CommentModule.notification', "{displayName} commented {contentTitle}.", [
                    'displayName' => Html::tag('strong', Html::encode($this->originator->displayName)),
                    'contentTitle' => $contentInfo
        ]);
    }

    /**
     * The commented record e.g. a Post
     * 
     * @return \humhub\modules\content\components\ContentActiveRecord
     */
    public function getCommentedRecord()
    {
        return $this->source->getCommentedRecord();
    }
}

?>
