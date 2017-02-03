<?php

use yii\helpers\Url;
use yii\helpers\Html;

?>

<?php $this->beginPage() ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <meta name="viewport" content="initial-scale=1.0"/>
        <meta name="format-detection" content="telephone=no"/>

        <title><?php echo Html::encode(Yii::$app->name); ?></title>
        <link href='http://fonts.googleapis.com/css?family=Open+Sans:300,100,400,600' rel='stylesheet' type='text/css'>
            <style type="text/css">

                <?php $defaultBackground =  Yii::$app->view->theme->variable('background-color-main', '#fff') ?>
                <?php $colorPrimary =  Yii::$app->view->theme->variable('primary', '#708fa0') ?>
                
                .ReadMsgBody {
                    width: 100%;
                    background-color: <?= $defaultBackground ?>;
                }

                .ExternalClass {
                    width: 100%;
                    background-color: <?= $defaultBackground ?>;
                }

                .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {
                    line-height: 100%;
                }

                html {
                    width: 100%;
                }

                body {
                    -webkit-text-size-adjust: none;
                    -ms-text-size-adjust: none;
                }

                body {
                    margin: 0;
                    padding: 0;
                }

                table {
                    border-spacing: 0;
                }

                img {
                    display: block !important;
                }

                table td {
                    border-collapse: collapse;
                }

                .yshortcuts a {
                    border-bottom: none !important;
                }

                html, body {
                    background-color: #ededed;
                    margin: 0;
                    padding: 0;
                }

                img {
                    height: auto;
                    line-height: 100%;
                    outline: none;
                    text-decoration: none;
                    display: block;
                }

                br, strong br, b br, em br, i br {
                    line-height: 100%;
                }

                h1, h2, h3, h4, h5, h6 {
                    line-height: 100% !important;
                    -webkit-font-smoothing: antialiased;
                }

                h1 a, h2 a, h3 a, h4 a, h5 a, h6 a {
                    color: <?= Yii::$app->view->theme->variable('info', '#6fdbe8') ?> !important;
                }

                h1 a:active, h2 a:active, h3 a:active, h4 a:active, h5 a:active, h6 a:active {
                    color: <?= Yii::$app->view->theme->variable('info', '#6fdbe8') ?> !important;
                }

                h1 a:visited, h2 a:visited, h3 a:visited, h4 a:visited, h5 a:visited, h6 a:visited {
                    color: <?= Yii::$app->view->theme->variable('info', '#6fdbe8') ?> !important;
                }

                table td, table tr {
                    border-collapse: collapse;
                }

                .yshortcuts, .yshortcuts a, .yshortcuts a:link, .yshortcuts a:visited, .yshortcuts a:hover, .yshortcuts a span {
                    color: black;
                    text-decoration: none !important;
                    border-bottom: none !important;
                    background: none !important;
                }

                code {
                    white-space: 300;
                    word-break: break-all;
                }

                span a {
                    text-decoration: none !important;
                }

                a {
                    text-decoration: none !important;
                }

                .default-edit-image {
                    height: 20px;
                }

                .nav-ul {
                    margin-left: -23px !important;
                    margin-top: 0px !important;
                    margin-bottom: 0px !important;
                }

                img {
                    height: auto !important;
                }

                td[class="image-270px"] img {
                    width: 270px !important;
                    height: auto !important;
                    max-width: 270px !important;
                }

                td[class="image-170px"] img {
                    width: 170px !important;
                    height: auto !important;
                    max-width: 170px !important;
                }

                td[class="image-185px"] img {
                    width: 185px !important;
                    height: auto !important;
                    max-width: 185px !important;
                }

                td[class="image-124px"] img {
                    width: 124px !important;
                    height: auto !important;
                    max-width: 124px !important;
                }

                @media only screen and (max-width: 640px) {
                    body {
                        width: auto !important;
                    }

                    table[class="container"] {
                        width: 100% !important;
                        padding-left: 20px !important;
                        padding-right: 20px !important;
                    }

                    td[class="image-270px"] img {
                        width: 100% !important;
                        height: auto !important;
                        max-width: 100% !important;
                    }

                    td[class="image-170px"] img {
                        width: 100% !important;
                        height: auto !important;
                        max-width: 100% !important;
                    }

                    td[class="image-185px"] img {
                        width: 185px !important;
                        height: auto !important;
                        max-width: 185px !important;
                    }

                    td[class="image-124px"] img {
                        width: 100% !important;
                        height: auto !important;
                        max-width: 100% !important;
                    }

                    td[class="image-100-percent"] img {
                        width: 100% !important;
                        height: auto !important;
                        max-width: 100% !important;
                    }

                    td[class="small-image-100-percent"] img {
                        width: 100% !important;
                        height: auto !important;
                    }

                    table[class="full-width"] {
                        width: 100% !important;
                    }

                    table[class="full-width-text"] {
                        width: 100% !important;
                        background-color: <?= $defaultBackground ?>;
                        padding-left: 20px !important;
                        padding-right: 20px !important;
                    }

                    table[class="full-width-text2"] {
                        width: 100% !important;
                        background-color: <?= $defaultBackground ?>;
                        padding-left: 20px !important;
                        padding-right: 20px !important;
                    }

                    table[class="col-2-3img"] {
                        width: 50% !important;
                        margin-right: 20px !important;
                    }

                    table[class="col-2-3img-last"] {
                        width: 50% !important;
                    }

                    table[class="col-2-footer"] {
                        width: 55% !important;
                        margin-right: 20px !important;
                    }

                    table[class="col-2-footer-last"] {
                        width: 40% !important;
                    }

                    table[class="col-2"] {
                        width: 47% !important;
                        margin-right: 20px !important;
                    }

                    table[class="col-2-last"] {
                        width: 47% !important;
                    }

                    table[class="col-3"] {
                        width: 29% !important;
                        margin-right: 20px !important;
                    }

                    table[class="col-3-last"] {
                        width: 29% !important;
                    }

                    table[class="row-2"] {
                        width: 50% !important;
                    }

                    td[class="text-center"] {
                        text-align: center !important;
                    }

                    table[class="remove"] {
                        display: none !important;
                    }

                    td[class="remove"] {
                        display: none !important;
                    }

                    table[class="fix-box"] {
                        padding-left: 20px !important;
                        padding-right: 20px !important;
                    }

                    td[class="fix-box"] {
                        padding-left: 20px !important;
                        padding-right: 20px !important;
                    }

                    td[class="font-resize"] {
                        font-size: 18px !important;
                        line-height: 22px !important;
                    }

                    table[class="space-scale"] {
                        width: 100% !important;
                        float: none !important;
                    }

                    table[class="clear-align-640"] {
                        float: none !important;
                    }

                }

                @media only screen and (max-width: 479px) {
                    body {
                        font-size: 10px !important;
                    }

                    table[class="container"] {
                        width: 100% !important;
                        padding-left: 10px !important;
                        padding-right: 10px !important;
                    }

                    table[class="container2"] {
                        width: 100% !important;
                        float: none !important;

                    }

                    td[class="full-width"] img {
                        width: 100% !important;
                        height: auto !important;
                        max-width: 100% !important;
                        min-width: 124px !important;
                    }

                    td[class="image-270px"] img {
                        width: 100% !important;
                        height: auto !important;
                        max-width: 100% !important;
                        min-width: 124px !important;
                    }

                    td[class="image-170px"] img {
                        width: 100% !important;
                        height: auto !important;
                        max-width: 100% !important;
                        min-width: 124px !important;
                    }

                    td[class="image-185px"] img {
                        width: 185px !important;
                        height: auto !important;
                        max-width: 185px !important;
                        min-width: 124px !important;
                    }

                    td[class="image-124px"] img {
                        width: 100% !important;
                        height: auto !important;
                        max-width: 100% !important;
                        min-width: 124px !important;
                    }

                    td[class="image-100-percent"] img {
                        width: 100% !important;
                        height: auto !important;
                        max-width: 100% !important;
                        min-width: 124px !important;
                    }

                    td[class="small-image-100-percent"] img {
                        width: 100% !important;
                        height: auto !important;
                        max-width: 100% !important;
                        min-width: 124px !important;
                    }

                    table[class="full-width"] {
                        width: 100% !important;
                    }

                    table[class="full-width-text"] {
                        width: 100% !important;
                        background-color: <?= $defaultBackground ?>;
                        padding-left: 20px !important;
                        padding-right: 20px !important;
                    }

                    table[class="full-width-text2"] {
                        width: 100% !important;
                        background-color: <?= $defaultBackground ?>;
                        padding-left: 20px !important;
                        padding-right: 20px !important;
                    }

                    table[class="col-2-footer"] {
                        width: 100% !important;
                        margin-right: 0px !important;
                    }

                    table[class="col-2-footer-last"] {
                        width: 100% !important;
                    }

                    table[class="col-2"] {
                        width: 100% !important;
                        margin-right: 0px !important;
                    }

                    table[class="col-2-last"] {
                        width: 100% !important;

                    }

                    table[class="col-3"] {
                        width: 100% !important;
                        margin-right: 0px !important;
                    }

                    table[class="col-3-last"] {
                        width: 100% !important;

                    }

                    table[class="row-2"] {
                        width: 100% !important;
                    }

                    table[id="col-underline"] {
                        float: none !important;
                        width: 100% !important;
                        border-bottom: 1px solid <?= Yii::$app->view->theme->variable('background-color-page', '#ededed') ?>;
                    }

                    td[id="col-underline"] {
                        float: none !important;
                        width: 100% !important;
                        border-bottom: 1px solid <?= Yii::$app->view->theme->variable('background-color-page', '#ededed') ?>;
                    }

                    td[class="col-underline"] {
                        float: none !important;
                        width: 100% !important;
                        border-bottom: 1px solid <?= Yii::$app->view->theme->variable('background-color-page', '#ededed') ?>;
                    }

                    td[class="text-center"] {
                        text-align: center !important;

                    }

                    div[class="text-center"] {
                        text-align: center !important;
                    }

                    table[id="clear-padding"] {
                        padding: 0 !important;
                    }

                    td[id="clear-padding"] {
                        padding: 0 !important;
                    }

                    td[class="clear-padding"] {
                        padding: 0 !important;
                    }

                    table[class="remove-479"] {
                        display: none !important;
                    }

                    td[class="remove-479"] {
                        display: none !important;
                    }

                    table[class="clear-align"] {
                        float: none !important;
                    }

                    table[class="width-small"] {
                        width: 100% !important;
                    }

                    table[class="fix-box"] {
                        padding-left: 0px !important;
                        padding-right: 0px !important;
                    }

                    td[class="fix-box"] {
                        padding-left: 0px !important;
                        padding-right: 0px !important;
                    }

                    td[class="font-resize"] {
                        font-size: 14px !important;
                    }

                    td[class="increase-Height"] {
                        height: 10px !important;
                    }

                    td[class="increase-Height-20"] {
                        height: 20px !important;
                    }

                }

                @media only screen and (max-width: 320px) {
                    table[class="width-small"] {
                        width: 125px !important;
                    }

                    img[class="image-100-percent"] {
                        width: 100% !important;
                        height: auto !important;
                        max-width: 100% !important;
                        min-width: 124px !important;
                    }

                }
            </style>

            <?php $this->head() ?>
    </head>

    <body style="font-size:12px; font-family:Open Sans, Arial,Tahoma, Helvetica, sans-serif; background-color:<?= Yii::$app->view->theme->variable('background-color-page', '#ededed') ?>; ">
        <?php $this->beginBody() ?>

        <!--start 100% wrapper (white background) -->
        <table width="100%" id="mainStructure" border="0" cellspacing="0" cellpadding="0" style="background-color:<?= Yii::$app->view->theme->variable('background-color-page', '#ededed') ?>;">


            <!-- START VIEW HEADER -->
            <tr>
                <td align="center" valign="top" style="background-color: <?= $colorPrimary ?>; ">

                    <!-- start container 600 -->
                    <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="background-color: <?= $colorPrimary ?>;">
                        <tr>
                            <td valign="top">

                                <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" style="background-color: <?= $colorPrimary ?>; ">
                                    <tr>
                                        <td valign="top" height="10"></td>
                                    </tr>
                                    <tr>
                                        <td valign="top">

                                            <!-- start container -->
                                            <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0">

                                                <tr>
                                                    <td valign="top">

                                                        <!-- start view online -->
                                                        <table align="left" border="0" cellspacing="0" cellpadding="0"
                                                               class="container2">
                                                            <tr>
                                                                <td>
<!-- Header app name begin-->
                                                                    <table align="center" border="0" cellspacing="0" cellpadding="0">
                                                                        <tr>
                                                                            <td style="text-align:center;">
                                                                                <span style="text-decoration: none; color:<?= Yii::$app->view->theme->variable('text-color-contrast', '#ffffff') ?>;">
                                                                                    <a href="<?php echo Url::to(['/'], true); ?>"
                                                                                        style="font-size: 18px; line-height: 27px; font-family:Open Sans, Arial,Tahoma, Helvetica, sans-serif; color:<?= Yii::$app->view->theme->variable('text-color-contrast', '#ffffff') ?>; font-weight:600; text-align:left;">
                                                                                            <?php echo Html::encode(Yii::$app->name); ?>
                                                                                    </a>
                                                                                </span>
                                                                            </td>
                                                                        </tr>
                                                                    </table>
<!-- Header app name end-->
                                                                </td>
                                                            </tr>
                                                            <!-- start space -->
                                                            <tr>
                                                                <td valign="top" class="increase-Height">
                                                                </td>
                                                            </tr>
                                                            <!-- end space -->
                                                        </table>
                                                        <!-- end view online -->

                                                    </td>
                                                </tr>
                                            </table>
                                            <!-- end container  -->
                                        </td>
                                    </tr>

                                    <!-- start space -->
                                    <tr>
                                        <td valign="top" height="10">
                                        </td>
                                    </tr>
                                    <!-- end space -->

                                    <!-- start space -->
                                    <tr>
                                        <td valign="top" class="increase-Height">
                                        </td>
                                    </tr>
                                    <!-- end space -->

                                </table>
                                <!-- end container 600-->
                            </td>
                        </tr>
                    </table>

                </td>
            </tr>
            <!-- END VIEW HEADER -->


            <!--START TOP NAVIGATION ​LAYOUT-->
            <tr>
                <td valign="top">
                    <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0">


                        <!-- START CONTAINER NAVIGATION -->
                        <tr>
                            <td height="30">

                            </td>
                        </tr>


                        <!-- END CONTAINER NAVIGATION -->

                    </table>
                </td>
            </tr>
            <!--END TOP NAVIGATION ​LAYOUT-->


            <!-- START HEIGHT SPACE 20PX LAYOUT-1 -->
            <tr>
                <td valign="top" align="center" class="fix-box">
                    <table width="600" height="20" align="center" border="0" cellspacing="0" cellpadding="0"
                           style="background-color: <?= $defaultBackground ?>; border-top-left-radius: 4px; border-top-right-radius: 4px;" class="full-width">
                        <tr>
                            <td valign="top" height="20">
                        </tr>
                    </table>
                </td>
            </tr>
            <!-- END HEIGHT SPACE 20PX LAYOUT-1-->


            <!-- START EMAIL CONTENT -->

            <?= $content ?>

            <!-- END EMAIL CONTENT -->

            <!--START FOOTER LAYOUT-->
            <tr>
                <td valign="top">
                    <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0">


                        <!-- START CONTAINER  -->
                        <tr>
                            <td align="center" valign="top">

                                <!-- start footer container -->
                                <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container">

                                    <tr>
                                        <td valign="top">

                                            <!-- start footer -->
                                            <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width">

                                                <!-- start space -->
                                                <tr>
                                                    <td valign="top" height="20">
                                                    </td>
                                                </tr>
                                                <!-- end space -->

                                                <tr>
                                                    <td valign="middle">
                                                        <?php $soft2Color = Yii::$app->view->theme->variable('text-color-soft2', '#aeaeae')?>    
                                                        <table align="center" border="0" cellspacing="0" cellpadding="0" class="container2">

                                                            <tr>
                                                                <td align="center" valign="top" style="font-size: 11px;  line-height: 18px; font-weight:300; text-align: center; font-family:Open Sans,Arail,Tahoma, Helvetica, Arial, sans-serif;">
                         
                                                                    <?php if (isset(Yii::$app->view->params['showUnsubscribe']) && Yii::$app->view->params['showUnsubscribe'] === true) : ?>
                                                                        <?php $url = (isset(Yii::$app->view->params['unsubscribeUrl'])) ? Yii::$app->view->params['unsubscribeUrl'] : \yii\helpers\Url::to(['/notification/user'], true) ?>
                                                                        <span style="text-decoration: none; color: <?= $soft2Color ?>;">
                                                                            <a href="<?= $url ?>" style="text-decoration: none; color: <?= $soft2Color ?>;"><?= Yii::t('base', 'Unsubscribe') ?></a>
                                                                            • 
                                                                        </span> 
                                                                    <?php endif; ?>

                                                                    <span style="text-decoration: none; color:<?= $soft2Color ?>;">
                                                                        Powered by <a href="http://www.humhub.org"  style="text-decoration: none; color: <?= $soft2Color ?>;">HumHub</a> 
                                                                    </span>

                                                                </td>

                                                            </tr>

                                                            <!-- start space -->
                                                            <tr>
                                                                <td valign="top" class="increase-Height-20">
                                                                </td>
                                                            </tr>
                                                            <!-- end space -->

                                                        </table>

                                                    </td>
                                                </tr>

                                                <!-- start space -->
                                                <tr>
                                                    <td valign="top" height="20">
                                                    </td>
                                                </tr>
                                                <!-- end space -->

                                            </table>
                                            <!-- end footer -->
                                        </td>
                                    </tr>
                                </table>
                                <!-- end footer container -->

                            </td>
                        </tr>

                        <!-- END CONTAINER  -->

                    </table>
                </td>
            </tr>
            <!--END FOOTER ​LAYOUT-->

        </table>
        <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>