<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\notifications;

use Yii;
use yii\bootstrap\Html;
use humhub\modules\notification\components\BaseNotification;


class UserAddedNotification extends BaseNotification
{
    /**
     * @inheritdoc
     */
    public $moduleId = 'space';

    /**
     * @inheritdoc
     */
    public $viewName = 'userAdded';


    /**
     *  @inheritdoc
     */
    public function category()
    {
        return new SpaceMemberNotificationCategory;
    }

    /**
     *  @inheritdoc
     */
    public function getMailSubject()
    {
        return $this->getInfoText($this->getSpace()->getDisplayName());
    }

    /**
     * @inheritdoc
     */
    public function html()
    {
        return $this->getInfoText(Html::tag('strong', Html::encode($this->getSpace()->getDisplayName())));
    }

    private function getInfoText($spaceName)
    {
        return Yii::t('SpaceModule.notification', 'You were added to Space {spaceName}', [
            '{spaceName}' => $spaceName
        ]);
    }

}
