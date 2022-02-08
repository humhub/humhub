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
     * @var string
     */
    public $message;

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
        return Yii::t('ContentModule.notifications', 'Your content was deleted by {displayName}. Reason: {message}', [
            'displayName' => Html::tag('strong', Html::encode($this->originator->displayName)),
            'message' => $this->record->message
        ]);
    }

    /**
     * Set a `message` property
     */
    public function commented($message)
    {
        if (!$message) {
            return $this;
        }

        $this->message = $message;
        $this->record->message = $message;

        return $this;
    }
}

?>
