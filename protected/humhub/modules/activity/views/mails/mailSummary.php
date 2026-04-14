<?php

/* @var $activities string */

use humhub\helpers\MailStyleHelper;

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

<?= $activities ?>
