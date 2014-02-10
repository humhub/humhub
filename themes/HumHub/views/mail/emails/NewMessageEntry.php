

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.5"/>
    <title>HumHub Email Template</title>

    <style type="text/css">
            /*////// RESET STYLES //////*/
        body, #bodyTable, #bodyCell{height:100% !important; margin:0; padding:0; width:100% !important;}
        table{border-collapse:collapse;}
        img, a img{border:0; outline:none; text-decoration:none;}
        h1, h2, h3, h4, h5, h6{margin:0; padding:0;}
        p{margin: 1em 0;}

            /*////// CLIENT-SPECIFIC STYLES //////*/
        .ReadMsgBody{width:100%;} .ExternalClass{width:100%;} /* Force Hotmail/Outlook.com to display emails at full width. */
        .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div{line-height:100%;} /* Force Hotmail/Outlook.com to display line heights normally. */
        table, td{mso-table-lspace:0pt; mso-table-rspace:0pt;} /* Remove spacing between tables in Outlook 2007 and up. */
        #outlook a{padding:0;} /* Force Outlook 2007 and up to provide a "view in browser" message. */
        img{-ms-interpolation-mode: bicubic;} /* Force IE to smoothly render resized images. */
        body, table, td, p, a, li, blockquote{-ms-text-size-adjust:100%; -webkit-text-size-adjust:100%;} /* Prevent Windows- and Webkit-based mobile platforms from changing declared text sizes. */

            /*////// GENERAL STYLES //////*/
        body, #bodyTable{background-color:#EDEDEC;}
        #bodyCell{padding-top:20px; padding-bottom:40px;}
        #emailBody{background-color:#FFFFFF; border-radius: 4px;}
        .flexibleContainerCell{padding-top:20px; padding-Right:20px; padding-Left:20px;}
        .flexibleImage{height:auto;}
        .bottomShim{padding-bottom:20px;}

        h1, h2, h3, h4, h5, h6{color:#202020; font-family:Helvetica; font-size:20px; line-height:125%; text-align:Left;}
        h3 {font-size:18px;}

        .textContent, .textContentLast{color:#404040; font-family:Helvetica; font-size:13px; line-height:125%; text-align:Left; padding-bottom:20px;}
        .textContent a, .textContentLast a{color:#4cd9c0; text-decoration:none;}

        #footerTable {font-family:Helvetica; font-size: 9px; color: #999999; margin-top: 15px;}
        #footerTable a {color:#4cd9c0; text-decoration:none;}

        .imageContent, .imageContentLast{padding-bottom:20px;}

        .nestedContainer{background-color:#E5E5E5; border:1px solid #CCCCCC;}
        .nestedContainerCell{padding-top:20px; padding-Right:20px; padding-Left:20px;}

        .emailButton{background-color:#4cd9c0; border-collapse:separate; border-radius:4px;}
        .buttonContent{color:#FFFFFF; font-family:Helvetica; font-size:18px; font-weight:bold; line-height:100%; padding:15px; text-align:center;}
        .buttonContent a{color:#FFFFFF; display:block; text-decoration:none; width:100%;}

            /*////// MOBILE STYLES //////*/
        @media only screen and (max-width: 480px){
            /*////// CLIENT-SPECIFIC STYLES //////*/
            body{width:100% !important; min-width:100% !important;} /* Force iOS Mail to render the email at full width. */

            /*////// GENERAL STYLES //////*/
            td[id="bodyCell"]{padding-top:10px !important; padding-Right:10px !important; padding-Left:10px !important;}
            table[id="emailBody"]{width:100% !important;}

            table[class="flexibleContainer"]{display:block !important; width:100% !important;}
            img[class="flexibleImage"]{width:100% !important;}
            table[class="emailButton"]{width:100% !important;}

            td[class="textContentLast"], td[class="imageContentLast"]{padding-top:20px !important;}
        }
    </style>
    <!--
        Outlook Conditional CSS

        These two style blocks target Outlook 2007 & 2010 specifically, forcing
        columns into a single vertical stack as on mobile clients. This is
        primarily done to avoid the 'page break bug' and is optional.

        More information here:
        http://templates.mailchimp.com/development/css/outlook-conditional-css
    -->
    <!--[if mso 12]>
    <style type="text/css">
        .flexibleContainer{display:block !important; width:100% !important;}
    </style>
    <![endif]-->
    <!--[if mso 14]>
    <style type="text/css">
        .flexibleContainer{display:block !important; width:100% !important;}
    </style>
    <![endif]-->
</head>
<body>
<center>
    <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="bodyTable">
        <tr>
            <td align="center" valign="top" id="bodyCell">
                <!-- EMAIL CONTAINER // -->
                <table border="0" cellpadding="0" cellspacing="0" width="600" id="emailBody">










                    <tr>
                        <td align="center" valign="top">
                            <!-- CENTERING TABLE // -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center" valign="top">
                                        <!-- FLEXIBLE CONTAINER // -->
                                        <table border="0" cellpadding="0" cellspacing="0" width="600" class="flexibleContainer">
                                            <tr>
                                                <td align="center" valign="top" width="600" class="flexibleContainerCell">


                                                    <!-- CONTENT TABLE // -->
                                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                        <tr>
                                                            <td valign="top" class="textContent">
                                                                <h3><?php echo Yii::t('MailModule.base', 'New message'); ?></h3>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <!-- // CONTENT TABLE -->


                                                    <!-- CONTENT TABLE // -->
                                                    <table align="Left" border="0" cellpadding="0" cellspacing="0" width="60"
                                                           class="flexibleContainer">
                                                        <tbody>
                                                        <tr>
                                                            <td align="Left" valign="top" class="imageContent">
                                                                <a href="<?php echo Yii::app()->createUrl('profile/show', array('guid' => $senderGuid)); ?>">
                                                                    <img src="<?php echo $sender->getProfileImage()->getUrl(); ?>"
                                                                         width="50" class="flexibleImage">
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                    <!-- // CONTENT TABLE -->


                                                    <!-- CONTENT TABLE // -->
                                                    <table align="Right" border="0" cellpadding="0" cellspacing="0" width="480" class="flexibleContainer">
                                                        <tbody><tr>
                                                            <td valign="top" class="textContent">
                                                                <strong><?php echo $senderName; ?></strong> <?php echo Yii::t('MailModule.base', 'send you a new message in'); ?> <strong><?php echo $message->title; ?></strong><br><br>
                                                                <br>
                                                                <?php echo $message->getLastEntry()->content; ?>
                                                            </td>
                                                        </tr>
                                                        </tbody></table>
                                                    <!-- // CONTENT TABLE -->




                                                </td>
                                            </tr>
                                        </table>


                                    </td>
                                </tr>
                            </table>
                            <!-- // CENTERING TABLE -->
                        </td>
                    </tr>
                    <!-- // MODULE ROW -->

                    <tr>
                        <td align="center" valign="top">
                            <!-- CENTERING TABLE // -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tbody><tr>
                                    <td align="center" valign="top">
                                        <!-- FLEXIBLE CONTAINER // -->
                                        <table border="0" cellpadding="0" cellspacing="0" width="600" class="flexibleContainer">
                                            <tbody><tr>
                                                <td align="center" valign="top" width="600" class="flexibleContainerCell bottomShim">


                                                    <!-- CONTENT TABLE // -->
                                                    <table border="0" cellpadding="0" cellspacing="0" width="260" class="emailButton">
                                                        <tbody><tr>
                                                            <td align="center" valign="middle" class="buttonContent">
                                                                <a href="<?php echo Yii::app()->createAbsoluteUrl('mail/mail/index', array('id'=>$message->id)); ?>" target="_blank"><?php echo Yii::t('MailModule.base', 'Reply now'); ?></a>
                                                            </td>
                                                        </tr>
                                                        </tbody></table>
                                                    <!-- // CONTENT TABLE -->


                                                </td>
                                            </tr>
                                            </tbody></table>
                                        <!-- // FLEXIBLE CONTAINER -->
                                    </td>
                                </tr>
                                </tbody></table>
                            <!-- // CENTERING TABLE -->
                        </td>
                    </tr>






                </table>
                <!-- // EMAIL CONTAINER -->
                <table border="0" cellpadding="0" cellspacing="0" width="600" id="footerTable">
                    <tr>
                        <td align="center" valign="top">
                            <?php echo Yii::t('MailModule.base', 'Copyright © 2013 by humhub'); ?> | <a href="<?php echo Yii::app()->createAbsoluteUrl('//user/account/emailing'); ?>"><?php echo Yii::t('MailModule.base', 'Email preferences') ?></a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</center>
</body>
</html>
