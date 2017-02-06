<?php

use yii\helpers\Html;

/* @var $space \humhub\modules\space\models\Space */
/* @var $originator humhub\modules\user\models\User */
/* @var $content string */
/* @var $isComment boolean */
/* @var $date string */



?>
<table width="100%" style="table-layout:fixed;" border="0" cellspacing="0" cellpadding="0" align="left">
    <tr>
        <!-- START: USER IMAGE COLUMN -->
        <td width="40" valign="top" align="left" style="padding-right:20px;">

            <?php if ($originator) : ?>
                <?= humhub\widgets\mails\MailContentContainerImage::widget(['container' => $originator]); ?>
            <?php endif; ?>

        </td>
        <!-- END: USER IMAGE COLUMN-->

        <!-- START: CONTENT AND ORIGINATOR DESCRIPTION -->
        <td valign="top">
            <?php if ($originator) : ?>
                <table width="100%" style="table-layout:fixed;" border="0" cellspacing="0" cellpadding="0" align="left">
                    <tr>
                        <td>
                            <a href="<?= $originator->createUrl('/user/profile', [], true) ?>" style="font-size: 15px; line-height: 22px; font-family:Open Sans,Arial,Tahoma, Helvetica, sans-serif; color:<?= Yii::$app->view->theme->variable('text-color-highlight', '#555') ?>; font-weight:300; text-align:left; ">
                                <?= Html::encode($originator->displayName) ?>
                            </a>
                            <?php if ($date) : ?>
                                <span style="font-size: 11px; line-height: 22px; font-family:Open Sans,Arial,Tahoma, Helvetica, sans-serif; color:<?= Yii::$app->view->theme->variable('text-color-soft', '#bebebe') ?>; font-weight:300; text-align:left; ">
                                    <?= \humhub\widgets\TimeAgo::widget(['timestamp' => $date]) ?>
                                </span>
                            <?php endif; ?>
                             <?php if ($space && !$isComment) : ?>
                                <span style="font-size: 11px; line-height: 22px; font-family:Open Sans,Arial,Tahoma, Helvetica, sans-serif; color:<?= Yii::$app->view->theme->variable('text-color-soft', '#bebebe') ?>; font-weight:300; text-align:left;">
                                    <?= Yii::t('ContentModule.views_wallLayout', 'in'); ?>
                                </span>
                                <span style="font-size: 11px; line-height: 22px; font-family:Open Sans,Arial,Tahoma, Helvetica, sans-serif; color:<?= Yii::$app->view->theme->variable('text-color-soft', '#bebebe') ?>; font-weight:bold; text-align:left;">
                                     <a style="font-size: 11px; line-height: 22px; font-family:Open Sans,Arial,Tahoma, Helvetica, sans-serif; color:<?= Yii::$app->view->theme->variable('text-color-soft', '#bebebe') ?>; font-weight:bold; text-align:left; " href="<?= $space->getUrl() ?>">
                                        <?= Html::encode($space->displayName) ?>
                                    </a>
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <?php if($isComment) : ?>
                            <td height="15" style="word-wrap:break-word;font-size: 14px; line-height: 22px; font-family:Open Sans,Arial,Tahoma, Helvetica, sans-serif; color:<?= Yii::$app->view->theme->variable('text-color-main', '#777') ?>; font-weight:300; text-align:left;">
                                <?= $content ?>
                            </td>
                        <?php else : ?>
                            <td height="15" style="font-size: 15px; line-height: 22px; font-family:Open Sans,Arial,Tahoma, Helvetica, sans-serif; color:<?= Yii::$app->view->theme->variable('text-color-soft2', '#aeaeae') ?>; font-weight:300; text-align:left; ">
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
            <td colspan="2" style="word-wrap:break-word;padding-top:5px; padding-bottom:5px; font-size: 14px; line-height: 22px; font-family:Open Sans,Arial,Tahoma, Helvetica, sans-serif; color:<?= Yii::$app->view->theme->variable('text-color-main', '#777') ?>; font-weight:300; text-align:left; border-top: 1px solid <?= Yii::$app->view->theme->variable('background-color-page', '#ededed') ?>;">

                <?= $content ?>

            </td>
        </tr>
    <?php endif; ?>
</table>