<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\space\models\Space;
use humhub\modules\ui\mail\MailStyle;
use humhub\modules\user\models\User;
use humhub\widgets\mails\MailContentContainerImage;
use humhub\widgets\TimeAgo;

/* @var $this View */
/* @var $space Space */
/* @var $originator User */
/* @var $content string */
/* @var $isComment boolean */
/* @var $date string */
?>
<table width="100%" style="table-layout:fixed;" border="0" cellspacing="0" cellpadding="0" align="left">
    <tr>
        <!-- START: USER IMAGE COLUMN -->
        <td width="40" valign="top" align="left" style="padding-right:20px">

            <?php if ($originator) : ?>
                <?= MailContentContainerImage::widget(['container' => $originator]) ?>
            <?php endif ?>

        </td>
        <!-- END: USER IMAGE COLUMN-->

        <!-- START: CONTENT AND ORIGINATOR DESCRIPTION -->
        <td valign="top">
            <?php if ($originator) : ?>
                <table width="100%" style="table-layout:fixed;" border="0" cellspacing="0" cellpadding="0" align="left">
                    <tr>
                        <td>
                            <a href="<?= $originator->createUrl('/user/profile', [], true) ?>" style="font-size: 15px; line-height: 22px; font-family:<?= MailStyle::getFontFamily() ?>; color:<?= MailStyle::getTextColorHighlight() ?>; font-weight:300; text-align:left">
                                <?= Html::encode($originator->displayName) ?>
                            </a>
                            <?php if ($date) : ?>
                                <span style="font-size: 11px; line-height: 22px; font-family:<?= MailStyle::getFontFamily() ?>; color:<?= MailStyle::getTextColorSoft() ?>; font-weight:300; text-align:left">
                                    <?= TimeAgo::widget(['timestamp' => $date]) ?>
                                </span>
                            <?php endif ?>
                             <?php if ($space && !$isComment) : ?>
                                <span style="font-size: 11px; line-height: 22px; font-family:<?= MailStyle::getFontFamily() ?>; color:<?= MailStyle::getTextColorSoft() ?>; font-weight:300; text-align:left">
                                    <?= Yii::t('ContentModule.base', 'in') ?>
                                </span>
                                <span style="font-size: 11px; line-height: 22px; font-family:<?= MailStyle::getFontFamily() ?>; color:<?= MailStyle::getTextColorSoft() ?>; font-weight:bold; text-align:left">
                                     <a style="font-size: 11px; line-height: 22px; font-family:<?= MailStyle::getFontFamily() ?>; color:<?= MailStyle::getTextColorSoft() ?>; font-weight:bold; text-align:left; " href="<?= $space->createUrl(null, [], true) ?>">
                                        <?= Html::encode($space->displayName) ?>
                                    </a>
                                </span>
                            <?php endif ?>
                        </td>
                    </tr>
                    <tr>
                        <?php if($isComment) : ?>
                            <td height="15" style="word-wrap:break-word;font-size: 14px; line-height: 22px; font-family:<?= MailStyle::getFontFamily() ?>; color:<?= MailStyle::getTextColorMain() ?>; font-weight:300; text-align:left">
                                <?= $content ?>
                            </td>
                        <?php else : ?>
                            <td height="15" style="font-size: 15px; line-height: 22px; font-family:<?= MailStyle::getFontFamily() ?>; color:<?= MailStyle::getTextColorSoft2() ?>; font-weight:300; text-align:left">
                                <?= Html::encode($originator->displayNameSub) ?>
                            </td>
                        <?php endif ?>
                    </tr>
                </table>
            <?php endif ?>
        </td>
        <!-- END: CONTENT AND ORIGINATOR DESCRIPTION -->
    </tr>
    <?php if(!$isComment) : ?>
        <tr>
            <td colspan="2" height="10"></td>
        </tr>
        <tr>
            <td colspan="2" style="word-wrap:break-word;padding-top:5px; padding-bottom:5px; font-size: 14px; line-height: 22px; font-family:<?= MailStyle::getFontFamily() ?>; color:<?= MailStyle::getTextColorMain() ?>; font-weight:300; text-align:left; border-top: 1px solid <?= MailStyle::getBackgroundColorPage() ?>">

                <?= $content ?>

            </td>
        </tr>
    <?php endif ?>
</table>
