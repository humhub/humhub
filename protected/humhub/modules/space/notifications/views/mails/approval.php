<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */
/* @var $this yii\web\View */
/* @var $viewable humhub\modules\space\notifications\ApprovalRequest */
/* @var $url string */
/* @var $date string */
/* @var $isNew bool */
/* @var $originator \humhub\modules\user\models\User */
/* @var $source yii\db\ActiveRecord */
/* @var $contentContainer ContentContainerActiveRecord */
/* @var $space humhub\modules\space\models\Space */
/* @var $record Notification */

/* @var $message string */

use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\notification\models\Notification;
use humhub\widgets\mails\MailButtonList;

?>

<?php $this->beginContent('@notification/views/layouts/mail.php', $_params_); ?>

    <table width="100%" border="0" cellspacing="0" cellpadding="0" align="left">
        <tr>
            <td style="font-size: 14px; line-height: 22px; font-family:Open Sans,Arial,Tahoma, Helvetica, sans-serif; color:<?= Yii::$app->view->theme->variable('text-color-highlight', '#555555') ?>; font-weight:300; text-align:left;">
                <?= $viewable->html(); ?>
            </td>
        </tr>
        <tr>
            <td height="10"></td>
        </tr>
        <tr>
            <td height="10" style="border-top: 1px solid #eee;"></td>
        </tr>
        <tr>
            <td>
                <?=
                humhub\widgets\mails\MailContentEntry::widget([
                    'originator' => $originator,
                    'receiver' => $record->user,
                    'content' => $message,
                    'date' => $date,
                    'space' => $space,
                    'isComment' => true
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
                MailButtonList::widget(['buttons' => [
                    humhub\widgets\mails\MailButton::widget(['url' => $url, 'text' => Yii::t('SpaceModule.notification', 'View Online')])
                ]]);
                ?>
            </td>
        </tr>
    </table>

<?php
$this->endContent();
