<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\ui\mail\DefaultMailStyle;
use humhub\modules\ui\view\components\View;
use humhub\widgets\mails\MailContentContainerImage;
use yii\helpers\Html;

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
                        <a href="<?= $url ?>" style="font-size: 15px; line-height: 22px; font-family:<?= $this->theme->variable('mail-font-family', DefaultMailStyle::DEFAULT_FONT_FAMILY) ?>; color:<?= $this->theme->variable('text-color-highlight', '#555555') ?>; font-weight:300; text-align:left">
                            <?= Html::encode($container->displayName) ?>
                        </a>
                    </td>
                </tr>
                <tr>
                    <td height="15" style="font-size: 15px; line-height: 22px; font-family:<?= $this->theme->variable('mail-font-family', DefaultMailStyle::DEFAULT_FONT_FAMILY) ?>; color:<?= $this->theme->variable('text-color-soft2', '#555555') ?>; font-weight:300; text-align:left">
                        <?= Html::encode($description) ?>
                    </td>
                </tr>
            </table>
        </td>
        <!-- END: CONTENT AND ORIGINATOR DESCRIPTION -->
    </tr>
</table>
