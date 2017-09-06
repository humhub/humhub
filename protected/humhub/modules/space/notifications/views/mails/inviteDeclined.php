<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/* @var $this yii\web\View */
/* @var $viewable humhub\modules\user\notifications\Followed */
/* @var $url string */
/* @var $date string */
/* @var $isNew boolean */
/* @var $isNew boolean */
/* @var $originator \humhub\modules\user\models\User */
/* @var $source yii\db\ActiveRecord */
/* @var $contentContainer \humhub\modules\content\components\ContentContainerActiveRecord */
/* @var $space humhub\modules\space\models\Space */
/* @var $record \humhub\modules\notification\models\Notification */
/* @var $html string */
/* @var $text string */
?>

<?php $this->beginContent('@notification/views/layouts/mail.php', $_params_); ?>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" align="left">
        <tr>
            <td style="font-size: 14px; line-height: 22px; font-family:Open Sans,Arial,Tahoma, Helvetica, sans-serif; color:#555555; font-weight:300; text-align:center;">
                <?= $viewable->html(); ?>
            </td>
        </tr>
        <tr>
            <td height="20"></td>
        </tr>
        <tr>
            <td>
                <?=

                \humhub\widgets\mails\MailButtonList::widget(['buttons' => [
                        humhub\widgets\mails\MailButton::widget(['url' => $url, 'text' => Yii::t('SpaceModule.notifications_mails', 'View Online')])
                ]]);

                ?>
            </td>
        </tr>
        <?php /*
        <tr>
            <td height="10"></td>
        </tr>
        <tr>
            <td style="border-top: 1px solid #eee;padding-top:10px;">

                <table width="100%" border="0" cellspacing="0" cellpadding="0" align="left">
                    <tr>
                        <td width="109"></td>
                        <td width="50"><?= \humhub\modules\notification\widgets\MailContentContainerImage::widget(['container' => $originator])?></td>
                        <td width="109"></td>
                        <td width="25"><img src="<?= \yii\helpers\Url::to('@web-static/img/mail_ico_no.png', true); ?>" /></td>
                        <td width="109"></td>
                        <td width="50"><?= \humhub\modules\notification\widgets\MailContentContainerImage::widget(['container' => $space])?></td>
                        <td></td>
                    </tr>
                </table>
            </td>
        </tr>
        */?>
    </table>
<?php $this->endContent();