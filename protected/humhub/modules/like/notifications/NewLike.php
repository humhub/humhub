<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\like\notifications;

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
    public static function getTitle()
    {
        return Yii::t('LikeModule.notifiations_NewLike', 'New Like');
    }

    /**
     * @inheritdoc
     */
    public function getGroupKey()
    {
        $model = $this->getLikedRecord();
        return $model->className() . '-' . $model->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAsHtml()
    {
        $contentInfo = $this->getContentInfo($this->getLikedRecord());

        if ($this->groupCount > 1) {
            return Yii::t('LikeModule.notification', "{displayNames} likes {contentTitle}.", array(
                        'displayNames' => $this->getGroupUserDisplayNames(),
                        'contentTitle' => $contentInfo
            ));
        }
        return Yii::t('LikeModule.notification', "{displayName} likes {contentTitle}.", array(
                    'displayName' => Html::tag('strong', Html::encode($this->originator->displayName)),
                    'contentTitle' => $contentInfo
        ));
    }

    /**
     * The liked record
     * 
     * @return \humhub\components\ActiveRecord
     */
    protected function getLikedRecord()
    {
        return $this->source->getSource();
    }

}
