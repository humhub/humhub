<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\notifications;

use Yii;
use yii\bootstrap\Html;
use humhub\modules\notification\components\BaseNotification;

/**
 * MentionedNotification is fired to all users which are mentionied
 * in a HActiveRecordContent or HActiveRecordContentAddon
 */
class Mentioned extends BaseNotification
{

    /**
     * @inheritdoc
     */
    public $moduleId = 'user';

    /**
     * inheritdoc
     */
    public function send(\humhub\modules\user\models\User $user)
    {
        // Do additional access check here, because the mentioned user may have
        // no access to the content
        if (!$this->source->content->canRead($user->id)) {
            return;
        }

        return parent::send($user);
    }

    /**
     * @inheritdoc
     */
    public static function getTitle()
    {
        return Yii::t('UserModule.notification', 'Mentioned');
    }

    /**
     * @inheritdoc
     */
    public function getAsHtml()
    {
        return Yii::t('UserModule.notification', '{displayName} mentioned you in {contentTitle}.', array(
                    'displayName' => Html::tag('strong', Html::encode($this->originator->displayName)),
                    'contentTitle' => $this->getContentInfo($this->source)
        ));
    }

}

?>
