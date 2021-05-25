<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\like\notifications;

use humhub\modules\content\interfaces\ContentOwner;
use Yii;
use yii\bootstrap\Html;
use humhub\modules\notification\components\BaseNotification;

/**
 * Notifies a user about likes of his objects (posts, comments, tasks & co)
 *
 * @since 0.5
 */
class NewLike extends BaseNotification
{

    /**
     * @inheritdoc
     */
    public $moduleId = 'like';

    /**
     * @inheritdoc
     */
    public $viewName = 'newLike';

    /**
     * @inheritdoc
     */
    public function category()
    {
        return new LikeNotificationCategory();
    }

    /**
     * @inheritdoc
     */
    public function getGroupKey()
    {
        $model = $this->getLikedRecord();
        return get_class($model) . '-' . $model->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getMailSubject()
    {
        $model = $this->getLikedRecord();

        if(!$model instanceof ContentOwner) {
            return '';
        }

        $contentInfo = $this->getContentPlainTextInfo($model);

        if ($this->groupCount > 1) {
            return Yii::t('LikeModule.notifications', "{displayNames} likes your {contentTitle}.", [
                        'displayNames' => $this->getGroupUserDisplayNames(false),
                        'contentTitle' => $contentInfo
            ]);
        }

        return Yii::t('LikeModule.notifications', "{displayName} likes your {contentTitle}.", [
                    'displayName' => $this->originator->displayName,
                    'contentTitle' => $contentInfo
        ]);
    }

    /**
     * @inheritdoc
     */
    public function html()
    {
        $model = $this->getLikedRecord();

        if(!$model instanceof ContentOwner) {
            return '';
        }

        $contentInfo = $this->getContentInfo($model);

        if ($this->groupCount > 1) {
            return Yii::t('LikeModule.notifications', "{displayNames} likes {contentTitle}.", [
                        'displayNames' => $this->getGroupUserDisplayNames(),
                        'contentTitle' => $contentInfo
            ]);
        }

        return Yii::t('LikeModule.notifications', "{displayName} likes {contentTitle}.", [
                    'displayName' => Html::tag('strong', Html::encode($this->originator->displayName)),
                    'contentTitle' => $contentInfo
        ]);
    }

    /**
     * The liked record
     *
     * @return \humhub\components\ActiveRecord
     */
    public function getLikedRecord()
    {
        return $this->source->getSource();
    }
}
