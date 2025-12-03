<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\View;
use humhub\modules\space\models\Space;
use humhub\helpers\MailStyleHelper;
use humhub\widgets\mails\MailButton;
use humhub\widgets\mails\MailButtonList;
use humhub\widgets\mails\MailContentContainerInfoBox;

/* @var $this View */
/* @var $viewable humhub\modules\user\notifications\Followed */
/* @var $url string */
/* @var $space Space */
/* @var $_params_ array */
?>
<?php $this->beginContent('@notification/views/layouts/mail.php', $_params_) ?>

    <table width="100%" border="0" cellspacing="0" cellpadding="0" align="left">
        <tr>
            <td style="font-size: 14px; line-height: 22px; font-family:<?= MailStyleHelper::getFontFamily() ?>; color:<?= MailStyleHelper::getTextColorHighlight() ?>; font-weight:300; text-align:left">
                <?= $viewable->html() ?>
            </td>
        </tr>
        <tr>
            <td height="10"></td>
        </tr>
        <tr>
            <td style="border-top: 1px solid #eee;padding-top:10px">
                <?= MailContentContainerInfoBox::widget(['container' => $space]) ?>
            </td>
        </tr>
        <tr>
            <td height="10"></td>
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
