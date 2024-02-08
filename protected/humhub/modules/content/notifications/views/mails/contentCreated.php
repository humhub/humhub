<?php

/* @var $this yii\web\View */
/* @var $viewable humhub\modules\content\notifications\ContentCreated */
/* @var $url string */
/* @var $date string */
/* @var $isNew bool */
/* @var $originator \humhub\modules\user\models\User */
/* @var $source yii\db\ActiveRecord */
/* @var $contentContainer ContentContainerActiveRecord */
/* @var $space humhub\modules\space\models\Space */

/* @var $record Notification */

use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\notification\models\Notification;
use humhub\widgets\mails\MailButtonList;

?>
<?php $this->beginContent('@notification/views/layouts/mail.php', $_params_); ?>

<?= humhub\widgets\mails\MailContentEntry::widget([
    'originator' => $originator,
    'receiver' => $record->user,
    'content' => $viewable->source,
    'date' => $date,
    'space' => $space
]) ?>

<?= MailButtonList::widget([
    'buttons' => [
        humhub\widgets\mails\MailButton::widget(['url' => $url, 'text' => Yii::t('ContentModule.notifications', 'View Online')])
    ]
]) ?>

<?php $this->endContent();
