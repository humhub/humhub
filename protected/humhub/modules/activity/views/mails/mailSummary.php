<?php

use humhub\helpers\Html;
use humhub\helpers\MailStyleHelper;
use yii\helpers\Url;

/* @var $activities string */
/* @var $unseenNotificationCount int */

$unseenNotificationCount ??= 0;
?>

<tr>
    <td align="center" valign="top" class="fix-box">

        <!-- start container width 600px -->
        <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container"
               style="background-color:<?= MailStyleHelper::getBackgroundColorMain(
               ) ?>; border-top-left-radius: 4px; border-top-right-radius: 4px;">
            <tr>
                <td valign="top">

                    <!-- start container width 560px -->
                    <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width"
                           style="background-color:<?= MailStyleHelper::getBackgroundColorMain() ?>;">

                        <!-- start image content -->
                        <tr>
                            <td valign="top" width="100%">

                                <!-- start content left -->
                                <table width="270" border="0" cellspacing="0" cellpadding="0" align="left"
                                       class="full-width">

                                    <!-- start text content -->
                                    <tr>
                                        <td valign="top">
                                            <table border="0" cellspacing="0" cellpadding="0" align="left">
                                                <tr>
                                                    <td style="font-size: 18px; line-height: 22px; font-family: <?= MailStyleHelper::getFontFamily(
                                                    ) ?>; font-weight:300; text-align:left;">
                                                        <span
                                                            style="color:<?= MailStyleHelper::getTextColorHighlight(
                                                            ) ?>; font-weight: 300;">
                                                            <a href="#"
                                                               style="text-decoration: none; color:<?= MailStyleHelper::getTextColorHighlight(
                                                               ) ?>; font-weight: 300;">
                                                                <?= Yii::t('base', '<strong>Mail</strong> summary') ?>
                                                            </a>
                                                        </span>
                                                    </td>
                                                </tr>

                                                <!--start space height -->
                                                <tr>
                                                    <td height="20"></td>
                                                </tr>
                                                <!--end space height -->
                                            </table>
                                        </td>
                                    </tr>
                                    <!-- end text content -->
                                </table>
                                <!-- end content left -->

                            </td>
                        </tr>
                        <!-- end image content -->

                    </table>
                    <!-- end container width 560px -->
                </td>
            </tr>
        </table>
        <!-- end  container width 600px -->
    </td>
</tr>

<?php if ($unseenNotificationCount > 0): ?>
<!-- START NOTIFICATIONS -->
<tr>
    <td align="center" valign="top" class="fix-box">
        <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container"
               style="background-color: <?= MailStyleHelper::getBackgroundColorMain() ?>">
            <tr>
                <td valign="top">
                    <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width">
                        <tr>
                            <td valign="top" align="left"
                                style="font-size: 14px; line-height: 22px; font-family: <?= MailStyleHelper::getFontFamily() ?>; color: <?= MailStyleHelper::getTextColorMain() ?>; font-weight: 300;">
                                <?= Html::encode(Yii::t(
                                    'NotificationModule.base',
                                    '{count,plural,=1{You have # unread notification} other{You have # unread notifications}}',
                                    ['count' => $unseenNotificationCount],
                                )) ?>
                                - <a href="<?= Html::encode(Url::to(['/notification/overview'], true)) ?>"
                                     style="text-decoration: none; color: <?= MailStyleHelper::getColorPrimary() ?>; font-weight: bold;"><?= Yii::t('NotificationModule.base', 'Open notifications') ?></a>
                            </td>
                        </tr>
                        <tr>
                            <td height="20"></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </td>
</tr>
<!-- END NOTIFICATIONS -->
<?php endif ?>

<?= $activities ?>
