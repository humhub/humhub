<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\helpers\MailStyleHelper;
use humhub\widgets\mails\MailContentContainerImage;

/* @var $this View */
/* @var $container ContentContainerActiveRecord */
/* @var $url string */
/* @var $description string */
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" align="left">
    <tr>
        <!-- START: Space IMAGE COLUMN -->
        <td width="40" valign="top" align="left" style="padding-right:20px">
            <?= MailContentContainerImage::widget(['container' => $container]) ?>
        </td>
        <!-- END: Space IMAGE COLUMN-->

        <!-- START: CONTENT AND ORIGINATOR DESCRIPTION -->
        <td valign="top">
            <table width="100%" border="0" cellspacing="0" cellpadding="0" align="left">
                <tr>
                    <td>
                        <a href="<?= $url ?>" style="font-size: 15px; line-height: 22px; font-family:<?= MailStyleHelper::getFontFamily() ?>; color:<?= MailStyleHelper::getTextColorHighlight() ?>; font-weight:300; text-align:left">
                            <?= Html::encode($container->displayName) ?>
                        </a>
                    </td>
                </tr>
                <tr>
                    <td height="15" style="font-size: 15px; line-height: 22px; font-family:<?= MailStyleHelper::getFontFamily() ?>; color:<?= MailStyleHelper::getTextColorSoft2() ?>; font-weight:300; text-align:left">
                        <?= Html::encode($description) ?>
                    </td>
                </tr>
            </table>
        </td>
        <!-- END: CONTENT AND ORIGINATOR DESCRIPTION -->
    </tr>
</table>
