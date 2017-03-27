<?php
/* @var $this yii\web\View */
/* @var $viewable humhub\modules\comment\notifications\NewComment */
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

<?php $comment = $viewable->source; ?>
<?php $contentRecord = $viewable->getCommentedRecord() ?>

<table width="100%" border="0" cellspacing="0" cellpadding="0" align="left">
    <tr>
        <td>
            <?=
            humhub\widgets\mails\MailCommentEntry::widget([
                'originator' => $originator,
                'comment' => $comment,
                'date' => $date,
                'space' => $space
            ]);
            ?>
        </td>
    </tr>
     <tr>
        <td height="20"></td>
    </tr>
     <tr>
        <td>
            <?=
            humhub\widgets\mails\MailHeadline::widget([
                'level' => 3,
                'text' => $contentRecord->getContentName().':',
                'style' => 'text-transform:capitalize;'
            ])
            ?>
        </td>
    </tr>
    <tr>
        <td style="padding:10px; border:1px solid <?= Yii::$app->view->theme->variable('background-color-secondary') ?>; border-radius:4px;">
            <?=
            humhub\widgets\mails\MailContentEntry::widget([
                'originator' => $contentRecord->owner,
                'content' => $contentRecord,
                'date' => $date,
                'space' => $space
            ]);
            ?>
        </td>
    </tr>
    <tr>
        <td height="10"></td>
    </tr>
    <tr>
        <td>
            <?=
            humhub\widgets\mails\MailButtonList::widget(['buttons' => [
                humhub\widgets\mails\MailButton::widget([
                    'url' => $url,
                    'text' => Yii::t('CommentModule.notifications_mails', 'View Online')
                ])
            ]]);
            ?>
        </td>
    </tr>
</table>
<?php $this->endContent(); ?>