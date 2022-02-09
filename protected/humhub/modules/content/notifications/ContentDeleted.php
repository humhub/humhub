<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\notifications;

use humhub\modules\notification\components\BaseNotification;
use Yii;
use yii\bootstrap\Html;
use humhub\modules\user\models\User;
use humhub\libs\Helpers;
use yii\helpers\Json;

/**
 * ContentDeletedNotification is fired when admin deletes a content (e.g. post)
 */
class ContentDeleted extends BaseNotification
{
    /**
     * @inheritdoc
     */
    public $requireSource = false;

    /**
     * @inheritdoc
     */
    public $moduleId = 'content';

    /**
     * @inheritdoc
     */
    public function category()
    {
        return new \humhub\modules\content\notifications\ContentCreatedNotificationCategory();
    }

    /**
     * @inheritdoc
     */
    public function html()
    {
        $this->payload = Json::decode($this->record->payload);

        return Yii::t('ContentModule.notifications', 'Your {contentTitle} was deleted by {displayName}. Reason: {message}', [
            'displayName' => Html::tag('strong', Html::encode($this->originator->displayName)),
            'contentTitle' => $this->payload['contentTitle'],
            'message' => $this->payload['message']
        ]);
    }

    /**
     * @inheritdoc
     */
    public function about($source)
    {
        if (!$source) {
            return $this;
        }

        $this->payload['contentTitle'] = $this->getContentInfo($source);

        return $this->payload();
    }


    /**
     * Set a `message` property
     */
    public function commented($message)
    {
        if (!$message) {
            return $this;
        }

        $this->payload['message'] = $message;

        return $this->payload();
    }
}

?>
