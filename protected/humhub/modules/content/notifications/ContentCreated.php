<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\notifications;

use Yii;
use yii\bootstrap\Html;

/**
 * ContentCreatedNotification is fired to all users which are manually selected
 * in ContentFormWidget to receive a notification.
 */
class ContentCreated extends \humhub\modules\notification\components\BaseNotification
{

    /**
     * @inheritdoc
     */
    public $moduleId = 'content';

    /**
     * @inheritdoc
     */
    public function getAsHtml()
    {
        return Yii::t('ContentModule.notifications_views_ContentCreated', '{displayName} created {contentTitle}.', array(
                    'displayName' => Html::tag('strong', Html::encode($this->originator->displayName)),
                    'contentTitle' => $this->getContentInfo($this->source)
        ));
    }

}

?>
