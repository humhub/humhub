<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\like\notifications;

use humhub\components\ActiveRecord;
use humhub\modules\content\interfaces\ContentOwner;
use humhub\modules\like\interfaces\LikeNotificationInterface;
use humhub\modules\notification\components\BaseNotification;
use Yii;
use yii\bootstrap\Html;
use yii\helpers\StringHelper;

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
     * @throws \Exception
     */
    public function getMailSubject()
    {
        $model = $this->getLikedRecord();

        $contentInfo = null;
        if ($model instanceof ContentOwner) {
            $contentInfo = $this->getContentPlainTextInfo($model);
        } elseif ($model instanceof LikeNotificationInterface) {
            $contentInfo = StringHelper::truncate($model->getLikeNotificationPlainTextPreview(), 60);
        }
        if (!$contentInfo) {
            return '';
        }

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
     * @throws \Exception
     */
    public function html()
    {
        $model = $this->getLikedRecord();

        $contentInfo = null;
        if ($model instanceof ContentOwner) {
            $contentInfo = $this->getContentInfo($model);
        } elseif ($model instanceof LikeNotificationInterface) {
            $contentInfo = StringHelper::truncate($model->getLikeNotificationHtmlPreview(), 60, '...', null, true);
        }
        if (!$contentInfo) {
            return '';
        }

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

    /**
     * The liked record
     *
     * @return ActiveRecord
     */
    public function getLikedRecord()
    {
        return $this->source->getSource();
    }

    /**
     * @inerhitdoc
     */
    public function getUrl()
    {
        $model = $this->getLikedRecord();

        if ($model instanceof ContentOwner) {
            return $model->getUrl(true);
        }

        if ($model instanceof LikeNotificationInterface) {
            return $model->getLikeNotificationUrl(true);
        }

        return parent::getUrl();
    }
}
