<?php

namespace humhub\modules\like\notifications;

use humhub\helpers\Html;
use humhub\modules\comment\models\Comment;
use humhub\modules\like\models\Like;
use humhub\modules\notification\components\BaseNotification;
use Yii;

/**
 * @property Like $source
 */
class NewLike extends BaseNotification
{
    public $moduleId = 'like';
    public $viewName = 'newLike';

    public function category()
    {
        return new LikeNotificationCategory();
    }

    public function getGroupKey()
    {
        $model = $this->source->getContentOwnerObject();
        return $model::class . '-' . $model->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getUrl()
    {
        $owner = $this->source->getContentOwnerObject();

        // A like on a comment links to the comment itself, otherwise to the liked content
        if ($owner instanceof Comment) {
            return $owner->getUrl(true);
        }

        return parent::getUrl();
    }

    public function getMailSubject()
    {
        $contentInfo = $this->getContentInfo($this->source->getContentOwnerObject());

        if ($this->groupCount > 1) {
            return Yii::t('LikeModule.notifications', "{displayNames} likes your {contentTitle}.", [
                'displayNames' => $this->getGroupUserDisplayNames(false),
                'contentTitle' => $contentInfo,
            ]);
        }

        return Yii::t('LikeModule.notifications', "{displayName} likes your {contentTitle}.", [
            'displayName' => $this->originator->displayName,
            'contentTitle' => $contentInfo,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function html()
    {
        $contentInfo = $this->getContentInfo($this->source->getContentOwnerObject());

        if ($this->groupCount > 1) {
            return Yii::t('LikeModule.notifications', "{displayNames} likes {contentTitle}.", [
                'displayNames' => $this->getGroupUserDisplayNames(),
                'contentTitle' => $contentInfo,
            ]);
        }

        return Yii::t('LikeModule.notifications', "{displayName} likes {contentTitle}.", [
            'displayName' => Html::tag('strong', Html::encode($this->originator->displayName)),
            'contentTitle' => $contentInfo,
        ]);
    }
}
