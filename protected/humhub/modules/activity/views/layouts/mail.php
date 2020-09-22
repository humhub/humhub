<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\space\models\Space;
use humhub\modules\ui\mail\DefaultMailStyle;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $space Space */
/* @var $url string */
/* @var $contentContainer ContentContainerActiveRecord */
/* @var $html string */
/* @var $text string */
/* @var $originator User */

?>

<!-- START NOTIFICATION/ACTIVITY -->
<tr>
    <td align="center" valign="top" class="fix-box">

        <!-- start  container width 600px -->
        <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" bgcolor="#ffffff"
               style="background-color: #ffffff; border-bottom-left-radius: 4px;">
            <tr>
                <td valign="top">

                    <!-- start container width 560px -->
                    <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width"
                           bgcolor="#ffffff" style="background-color:#ffffff;">

                        <!-- start image and content -->
                        <tr>
                            <td valign="top" width="100%">

                                <!-- start content left -->
                                <table width="100%" border="0" cellspacing="0" cellpadding="0" align="left">

                                    <!--start space height -->
                                    <tr>
                                        <td height="20"></td>
                                    </tr>
                                    <!--end space height -->


                                    <!-- start content top-->
                                    <tr>
                                        <td valign="top" align="left">

                                            <table border="0" cellspacing="0" cellpadding="0" align="left">
                                                <tr>

                                                    <td valign="top" align="left" style="padding-right:20px;">
                                                        <!-- START: USER IMAGE -->
                                                        <a href="<?= $originator->createUrl('/user/profile', [], true) ?>">
                                                            <img
                                                                src="<?= $originator->getProfileImage()->getUrl('', true) ?>"
                                                                width="50"
                                                                alt=""
                                                                style="max-width:50px; display:block !important; border-radius: 4px;"
                                                                border="0" hspace="0" vspace="0"/>
                                                        </a>
                                                        <!-- END: USER IMAGE -->
                                                    </td>


                                                    <td valign="top">

                                                        <table width="100%" border="0" cellspacing="0" cellpadding="0"
                                                               align="left">

                                                            <tr>
                                                                <td style="font-size: 13px; line-height: 22px; font-family: <?= Yii::$app->view->theme->variable('mail-font-family', DefaultMailStyle::DEFAULT_FONT_FAMILY) ?>; color:#555555; font-weight:300; text-align:left; ">
                                                                    <!-- prevent content overflow -->
                                                                    <div
                                                                        style="width:480px;overflow:hidden;text-overflow:ellipsis;font-size: 13px; line-height: 22px; font-family: <?= Yii::$app->view->theme->variable('mail-font-family', DefaultMailStyle::DEFAULT_FONT_FAMILY) ?>; color:#555555; font-weight:300; text-align:left;">
                                                                        <!-- content output-->
                                                                        <?= $content ?>

                                                                        <!-- check if activity object has a space -->
                                                                        <?php if ($space !== null): ?>
                                                                            <?= Html::a($space->displayName,
                                                                                $space->createUrl(null, [], true), [
                                                                                    'style' => 'text-decoration: none; color: #555555;'
                                                                                ]) ?>
                                                                        <?php endif; ?>

                                                                        <?php if ($url != '') : ?>
                                                                            <!-- START: CONTENT LINK -->
                                                                            <span
                                                                                style="text-decoration: none; color: <?= $this->theme->variable('primary') ?>;"> - <a
                                                                                    href="<?= $url ?>"
                                                                                    style="text-decoration: none; color: <?= $this->theme->variable('primary') ?>; "><?= Yii::t('ActivityModule.base', 'see online') ?></a></span>
                                                                            <!-- END: CONTENT LINK -->
                                                                        <?php endif; ?>
                                                                    </div>

                                                                </td>
                                                            </tr>

                                                        </table>

                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <!-- end  content top-->


                                    <!--start space height -->
                                    <tr>
                                        <td height="15" class="col-underline"></td>
                                    </tr>
                                    <!--end space height -->


                                </table>
                                <!-- end content left -->


                            </td>
                        </tr>
                        <!-- end image and content -->

                    </table>
                    <!-- end  container width 560px -->
                </td>
            </tr>
        </table>
        <!-- end  container width 600px -->
    </td>
</tr>
<!-- END NOTIFICATION/ACTIVITY -->
