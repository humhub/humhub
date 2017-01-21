<?php
/* @var $this yii\web\View */
/* @var $viewable humhub\modules\user\notifications\Mentioned */
/* @var $url string */
/* @var $date string */
/* @var $isNew boolean */
/* @var $isNew boolean */
/* @var $originator \humhub\modules\user\models\User */
/* @var source yii\db\ActiveRecord */
/* @var contentContainer \humhub\modules\content\components\ContentContainerActiveRecord */
/* @var space humhub\modules\space\models\Space */
/* @var record \humhub\modules\notification\models\Notification */
/* @var html string */
/* @var text string */
?>
<?php $this->beginContent('@notification/views/layouts/mail.php', $_params_); ?>
<?= humhub\modules\notification\widgets\MailContentEntry::widget([
    'originator' => $originator,
    'content' => $viewable->source,
    'date' => $date,
    'space' => $space
])
?>
<?php $this->endContent();