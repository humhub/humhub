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
            <table width="100%" style="background-color:#F5F5F5;border-radius:4px" border="0" cellspacing="0" cellpadding="0" align="left">
                <tr>
                    <td height="10"></td>
                </tr>
                <tr>
                    <td style="padding-left:10px;">
                        <?=
                        humhub\modules\notification\widgets\MailContentEntry::widget([
                            'originator' => $originator,
                            'content' => $comment,
                            'date' => $date,
                            'space' => $space,
                            'isComment' => true
                        ]);
                        ?>
                    </td>
                </tr>
                <tr>
                    <td height="10"></td>
                </tr>
            </table
        </td>
    </tr>
    <tr>
        <td height="10"></td>
    </tr>
    <tr>
        <td height="10" style="border-top: 1px solid #eee;"></td>
    </tr>
    <tr>
        <td >
            <?=
            humhub\modules\notification\widgets\MailContentEntry::widget([
                'originator' => $contentRecord->owner,
                'content' => $contentRecord,
                'date' => $date,
                'space' => $space
            ]);
            ?>
        </td>
    </tr>

</table>
<?php
$this->endContent();
