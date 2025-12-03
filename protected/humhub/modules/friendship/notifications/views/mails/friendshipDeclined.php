<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\View;
use humhub\modules\notification\components\BaseNotification;
use humhub\helpers\MailStyleHelper;
use humhub\modules\user\models\User;
use humhub\widgets\mails\MailButton;
use humhub\widgets\mails\MailButtonList;

/* @var $this View */
/* @var $viewable BaseNotification */
/* @var $url string */
/* @var $originator User */
/* @var $_params_ array */
?>

<?php $this->beginContent('@notification/views/layouts/mail.php', $_params_) ?>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" align="left">
        <tr>
            <td style="font-size: 14px; line-height: 22px; font-family:<?= MailStyleHelper::getFontFamily() ?>; color:<?= MailStyleHelper::getTextColorMain() ?>; font-weight:300; text-align:center">
                <?= $viewable->html() ?>
            </td>
        </tr>
        <tr>
            <td height="20"></td>
        </tr>
        <tr>
            <td>
                <?= MailButtonList::widget(['buttons' => [
                    MailButton::widget([
                        'url' => $url,
                        'text' => Yii::t('SpaceModule.notification', 'View Online'),
                    ]),
                ]]) ?>
            </td>
        </tr>
        <?php /*
        <tr>
            <td height="10"></td>
        </tr>
        <tr>
            <td style="border-top: 1px solid #eee;padding-top:10px">

                <table width="100%" border="0" cellspacing="0" cellpadding="0" align="left">
                    <tr>
                        <td width="109"></td>
                        <td width="50"><?= \humhub\modules\notification\widgets\MailContentContainerImage::widget(['container' => $originator])?></td>
                        <td width="109"></td>
                        <td width="25"><img src="<?= \yii\helpers\Url::to('@web-static/img/mail_ico_no.png', true) ?>" /></td>
                        <td width="109"></td>
                        <td width="50"><?= \humhub\modules\notification\widgets\MailContentContainerImage::widget(['container' => $space])?></td>
                        <td></td>
                    </tr>
                </table>
            </td>
        </tr>
        */ ?>
    </table>
<?php $this->endContent();
