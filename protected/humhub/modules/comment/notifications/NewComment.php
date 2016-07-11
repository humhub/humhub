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
    public function send(User $user)
    {
        // Check if there is also a mention notification, so skip this notification
        if (\humhub\modules\notification\models\Notification::find()->where(['class' => \humhub\modules\user\notifications\Mentioned::className(), 'user_id' => $user->id, 'source_class' => $this->source->className(), 'source_pk' => $this->source->getPrimaryKey()])->count() > 0) {
            return;
        }

        return parent::send($user);
    }

    /**
     * @inheritdoc
     */
    public static function getTitle()
    {
        return Yii::t('CommentModule.notifications_NewComment', 'New Comment');
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
    public function getAsHtml()
    {
        $contentInfo = $this->getContentInfo($this->getCommentedRecord());

        if ($this->groupCount > 1) {
            return Yii::t('CommentModule.notification', "{displayNames} commented {contentTitle}.", array(
                        'displayNames' => $this->getGroupUserDisplayNames(),
                        'contentTitle' => $contentInfo
            ));
        }
        return Yii::t('CommentModule.notification', "{displayName} commented {contentTitle}.", array(
                    'displayName' => Html::tag('strong', Html::encode($this->originator->displayName)),
                    'contentTitle' => $contentInfo
        ));
    }

    /**
     * The commented record
     * 
     * @return \humhub\components\ActiveRecord
     */
    protected function getCommentedRecord()
    {
        return $this->source->content->getPolymorphicRelation();
        ;
    }

}

?>
