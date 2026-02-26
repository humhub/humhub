<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\View;
use humhub\helpers\Html;
use humhub\helpers\MailStyleHelper;
use humhub\modules\user\models\User;
use humhub\widgets\mails\MailButton;
use humhub\widgets\mails\MailButtonList;

/* @var View $this */
/* @var string $linkPasswordReset */
/* @var User $user */
?>
<tr>
    <td align="center" valign="top" class="fix-box">

        <!-- start  container width 600px -->
        <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container"
               style="background-color: <?= MailStyleHelper::getBackgroundColorMain() ?>; ">

            <tr>
                <td valign="top">

                    <!-- start container width 560px -->
                    <table width="540" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width"
                           style="background-color:<?= MailStyleHelper::getBackgroundColorMain() ?>">

                        <!-- start text content -->
                        <tr>
                            <td valign="top">
                                <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
                                    <tr>
                                        <td valign="top" width="auto" align="center">
                                            <table border="0" align="center" cellpadding="0" cellspacing="0">
                                                <tr>
                                                    <td width="auto" align="center" valign="middle" height="28"
                                                        style="background-color: <?= MailStyleHelper::getBackgroundColorMain() ?>; background-clip: padding-box; font-size: 26px; font-family: <?= MailStyleHelper::getFontFamily() ?>; text-align: center; font-weight: 300; padding: 0 18px">
                                                        <span
                                                            style="color: <?= MailStyleHelper::getTextColorHighlight() ?>; font-weight: 300">
                                                            <?= Yii::t('UserModule.auth', '<strong>Password</strong> recovery') ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <!-- end text content -->

                    </table>
                    <!-- end  container width 560px -->

                </td>
            </tr>
        </table>
        <!-- end  container width 600px -->
    </td>
</tr>

<tr>
    <td align="center" valign="top" class="fix-box">

        <!-- start  container width 600px -->
        <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container"
               style="background-color: <?= MailStyleHelper::getBackgroundColorMain() ?>; border-radius: 0 0 4px 4px">


            <tr>
                <td valign="top">

                    <!-- start container width 560px -->
                    <table width="540" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width"
                           style="background-color:<?= MailStyleHelper::getBackgroundColorMain() ?>">


                        <!-- start text content -->
                        <tr>
                            <td valign="top">
                                <table width="100%" border="0" cellspacing="0" cellpadding="0" align="left">


                                    <!-- start text content -->
                                    <tr>
                                        <td valign="top">
                                            <table border="0" cellspacing="0" cellpadding="0" align="left">


                                                <!--start space height -->
                                                <tr>
                                                    <td height="30"></td>
                                                </tr>
                                                <!--end space height -->

                                                <tr>
                                                    <td style="font-size: 14px; line-height: 22px; font-family: <?= MailStyleHelper::getFontFamily() ?>; color: <?= MailStyleHelper::getTextColorMain() ?>; font-weight: 300; text-align: left">

                                                        <?= Yii::t('UserModule.auth', 'Hello {displayName}', ['{displayName}' => Html::encode($user->displayName)]) ?>
                                                        <br><br>
                                                        <?= Yii::t('UserModule.auth', 'Please use the following link within the next day to reset your password.') ?>
                                                        <br>
                                                        <?= Yii::t('UserModule.auth', "If you don't use this link within 24 hours, it will expire.") ?>
                                                        <br>

                                                    </td>
                                                </tr>

                                                <!--start space height -->
                                                <tr>
                                                    <td height="30"></td>
                                                </tr>
                                                <!--end space height -->


                                            </table>
                                        </td>
                                    </tr>
                                    <!-- end text content -->

                                    <tr>
                                        <td valign="top" width="auto" align="center">
                                            <?= MailButtonList::widget(['buttons' => [
                                                MailButton::widget([
                                                    'url' => $linkPasswordReset,
                                                    'text' => Yii::t('UserModule.auth', 'Reset Password'),
                                                ]),
                                            ]]) ?>
                                        </td>

                                    </tr>

                                </table>
                            </td>
                        </tr>
                        <!-- end text content -->

                        <!--start space height -->
                        <tr>
                            <td height="20"></td>
                        </tr>
                        <!--end space height -->

                    </table>
                    <!-- end  container width 560px -->
                </td>
            </tr>
        </table>
        <!-- end  container width 600px -->
    </td>
</tr>
