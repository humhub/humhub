<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */
?>
<table width="100%" style="background-color:<?= Yii::$app->view->theme->variable('background-color-secondary') ?>;border-radius:4px" border="0" cellspacing="0" cellpadding="0" align="left">
    <tr>
        <td height="10"></td>
    </tr>
    <tr>
        <td style="padding-left:10px;">
            <?= humhub\widgets\mails\MailContentEntry::widget([
                'originator' => $originator,
                'content' => $comment,
                'date' => $date,
                'space' => $space,
                'isComment' => true
            ]); ?>
        </td>
    </tr>
    <tr>
        <td height="10"></td>
    </tr>
</table>