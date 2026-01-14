<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/* @var $this \humhub\components\View */
/* @var $viewable humhub\modules\user\notifications\Followed */
/* @var $url string */
/* @var $_params_ array */

use humhub\helpers\MailStyleHelper;
use humhub\widgets\mails\MailButton;
use humhub\widgets\mails\MailButtonList;

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
    </table>
<?php $this->endContent();
