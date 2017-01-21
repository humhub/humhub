<?php

use yii\helpers\Html;

/* @var $space \humhub\modules\space\models\Space */
/* @var $originator humhub\modules\user\models\User */
/* @var $content string */
/* @var $isComment boolean */
/* @var $date string */



?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" align="left">
    <tr>
        <!-- START: USER IMAGE COLUMN -->
        <td width="40" valign="top" align="left" style="padding-right:20px;">

            <?php if ($originator) : ?>
                <?= humhub\modules\notification\widgets\MailContentContainerImage::widget(['container' => $originator]); ?>
            <?php endif; ?>

        </td>
        <!-- END: USER IMAGE COLUMN-->

        <!-- START: CONTENT AND ORIGINATOR DESCRIPTION -->
        <td valign="top">
            <?php if ($originator) : ?>
                <table width="100%" border="0" cellspacing="0" cellpadding="0" align="left">
                    <tr>
                        <td>
                            <a href="<?= $originator->createUrl('/user/profile', [], true) ?>" style="font-size: 15px; line-height: 22px; font-family:Open Sans,Arial,Tahoma, Helvetica, sans-serif; color:#555555; font-weight:300; text-align:left; ">
                                <?= $originator->displayName ?>
                            </a>
                            <?php if ($space && !$isComment) : ?>
                                <span style="font-size: 11px; line-height: 22px; font-family:Open Sans,Arial,Tahoma, Helvetica, sans-serif; color:#bebebe; font-weight:300; text-align:left; ">
                                    &#9658; <a style="font-size: 11px; line-height: 22px; font-family:Open Sans,Arial,Tahoma, Helvetica, sans-serif; color:#bebebe; font-weight:bold; text-align:left; " href="<?= $space->getUrl() ?>"><?= Html::encode($space->displayName) ?></a>
                                </span>
                            <?php endif; ?>
                            <?php if ($date) : ?>
                                <span style="font-size: 11px; line-height: 22px; font-family:Open Sans,Arial,Tahoma, Helvetica, sans-serif; color:#bebebe; font-weight:300; text-align:left; ">
                                    <?= \humhub\widgets\TimeAgo::widget(['timestamp' => $date]) ?>
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <?php if($isComment) : ?>
                            <td height="15" style="font-size: 14px; line-height: 22px; font-family:Open Sans,Arial,Tahoma, Helvetica, sans-serif; color:#555555; font-weight:300; text-align:left;">
                                <?= $content ?>
                            </td>
                        <?php else : ?>
                            <td height="15" style="font-size: 15px; line-height: 22px; font-family:Open Sans,Arial,Tahoma, Helvetica, sans-serif; color:#aeaeae; font-weight:300; text-align:left; ">
                                <?= Html::encode($originator->profile->title); ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                </table>
            <?php endif; ?>
        </td>
        <!-- END: CONTENT AND ORIGINATOR DESCRIPTION -->
    </tr>
    <?php if(!$isComment) : ?>
        <tr>
            <td colspan="2" height="10"></td>
        </tr>
        <tr>
            <td colspan="2" style="padding-top:5px; padding-bottom:5px; font-size: 14px; line-height: 22px; font-family:Open Sans,Arial,Tahoma, Helvetica, sans-serif; color:#555555; font-weight:300; text-align:left; border-top: 1px solid #eee;">

                <?= $content ?>

            </td>
        </tr>
    <?php endif; ?>
</table>