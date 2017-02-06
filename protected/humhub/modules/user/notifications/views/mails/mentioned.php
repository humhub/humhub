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
<table width="100%" border="0" cellspacing="0" cellpadding="0" align="left">
    <tr>
        <td>
            <?=
            humhub\widgets\mails\MailContentEntry::widget([
                'originator' => $originator,
                'content' => $viewable->source,
                'date' => $date,
                'space' => $space
            ])
            ?>
        </td>
    </tr>
    <tr>
        <td height="10"></td>
    </tr>
    <tr>
        <td>
            <?=
            \humhub\widgets\mails\MailButtonList::widget(['buttons' =>
                [
                    humhub\widgets\mails\MailButton::widget(['url' => $url, 'text' => Yii::t('UserModule.notifications_mails', 'View Online')
                    ])
            ]]);
            ?>
        </td>
    </tr>
</table>
<?php
$this->endContent();
