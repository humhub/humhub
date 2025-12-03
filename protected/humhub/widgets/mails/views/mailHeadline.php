<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\View;
use humhub\modules\ui\mail\MailStyle;

/* @var View $this */
/* @var int $level */
/* @var string $style */
/* @var string $text */

switch ($level) {
    case 3:
        $fontSize = '12px';
        $margin = '10';
        $weight = 'bold';
        break;
    case 2:
        $fontSize = '14px';
        $margin = '15';
        $weight = '300';
        break;
    default:
        $fontSize = '18px';
        $margin = '20';
        $weight = '300';
        break;
}
?>
<table border="0" cellspacing="0" cellpadding="0" align="left" >
    <tr>
        <td  style="font-size: <?= $fontSize ?>; line-height: 22px; font-family:<?= MailStyle::getFontFamily() ?>; color:<?= MailStyle::getTextColorHighlight() ?> font-weight:<?= $weight ?>; text-align:left">
            <span>
                <a href="#" style="text-decoration: none; color:<?= MailStyle::getTextColorHighlight() ?>; font-weight:<?= $weight ?>; <?= $style ?>"><?= $text ?></a>
            </span>
        </td>
    </tr>

    <!--start space height -->
    <tr>
        <td height="<?= $margin ?>"></td>
    </tr>
    <!--end space height -->
</table>
