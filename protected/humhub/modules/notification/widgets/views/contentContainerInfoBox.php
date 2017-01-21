<?php

use yii\helpers\Html;

/* @var $container \humhub\modules\content\components\ContentContainerActiveRecord */
/* @var $url string */
/* @var $description string */
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" align="left">
    <tr>
        <!-- START: Space IMAGE COLUMN -->
        <td width="40" valign="top" align="left" style="padding-right:20px;">
            <?= humhub\modules\notification\widgets\MailContentContainerImage::widget(['container' => $container]); ?>
        </td>
        <!-- END: Space IMAGE COLUMN-->

        <!-- START: CONTENT AND ORIGINATOR DESCRIPTION -->
        <td valign="top">
            <table width="100%" border="0" cellspacing="0" cellpadding="0" align="left">
                <tr>
                    <td>
                        <a href="<?= $url ?>" style="font-size: 15px; line-height: 22px; font-family:Open Sans,Arial,Tahoma, Helvetica, sans-serif; color:#555555; font-weight:300; text-align:left; ">
                            <?= Html::encode($container->displayName) ?>
                        </a>
                    </td>
                </tr>
                <tr>
                    <td height="15" style="font-size: 15px; line-height: 22px; font-family:Open Sans,Arial,Tahoma, Helvetica, sans-serif; color:#aeaeae; font-weight:300; text-align:left; ">
                        <?= $description ?>
                    </td>
                </tr>
            </table>
        </td>
        <!-- END: CONTENT AND ORIGINATOR DESCRIPTION -->
    </tr>
</table>