<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\notification\components\BaseNotification;
use humhub\modules\space\models\Space;
use humhub\modules\ui\mail\DefaultMailStyle;
use humhub\modules\ui\view\components\View;
use humhub\modules\user\models\User;
use humhub\widgets\mails\MailButton;
use humhub\widgets\mails\MailButtonList;
use humhub\widgets\mails\MailContentContainerImage;
use yii\helpers\Url;

/* @var $this View */
/* @var $viewable BaseNotification */
/* @var $url string */
/* @var $originator User */
/* @var $space Space */
/* @var $_params_ array */
?>
<?php $this->beginContent('@notification/views/layouts/mail.php', $_params_) ?>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" align="left">
        <tr>
            <td style="font-size: 14px; line-height: 22px; font-family:<?= $this->theme->variable('mail-font-family', DefaultMailStyle::DEFAULT_FONT_FAMILY) ?>; color:<?= $this->theme->variable('text-color-main', '#555') ?>; font-weight:300; text-align:left">
                <?= $viewable->html() ?>
            </td>
        </tr>
        <tr>
            <td height="10"></td>
        </tr>
        <tr>
            <td height="10" style="border-top: 1px solid #eee"></td>
        </tr>
        <tr>
            <td style="padding-top:10px">
                <table width="100%" border="0" cellspacing="0" cellpadding="0" align="left">
                    <tr>
                        <td width="109"></td>
                        <td width="50"><?= MailContentContainerImage::widget(['container' => $originator]) ?></td>
                        <td width="109"></td>
                        <td width="25"><img src="<?= Url::to('@web-static/img/mail_ico_check.png', true) ?>"/></td>
                        <td width="109"></td>
                        <td width="50"><?= MailContentContainerImage::widget(['container' => $space]) ?></td>
                        <td></td>
                    </tr>
                </table>
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
    </table>
<?php $this->endContent();
