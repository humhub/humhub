<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\ui\mail\DefaultMailStyle;
use humhub\modules\ui\view\components\View;

/* @var $this View */
/* @var $color string */
/* @var $text string */
/* @var $url string */
?>
<td width="auto"  align="center" valign="middle" height="32" 
    style="background-color:<?= $color ?>; border-radius:5px; background-clip: padding-box;font-size:14px; font-family:<?= $this->theme->variable('mail-font-family', DefaultMailStyle::DEFAULT_FONT_FAMILY) ?>; text-align:center;font-weight: 600; padding: 5px 30px">
    <span>
        <a href="<?= $url ?>" style="text-decoration: none; color: <?= $this->theme->variable('button-text-color', '#fff') ?>; font-weight: 300">
            <?= $text ?>
        </a>
    </span>
</td>
