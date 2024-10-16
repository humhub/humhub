<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\helpers\Html;
use humhub\modules\ui\menu\MenuLink;
use humhub\widgets\PoweredBy;

/* @var $this \humhub\modules\ui\view\components\View */
/* @var $entries MenuLink[] */
/* @var $options array */
/* @var $menu \humhub\widgets\FooterMenu */

?>

<div class="footer-nav footer-nav-sidebar">
    <small>
        <?php foreach ($entries as $k => $entry): ?>
            <?php if ($entry instanceof MenuLink): ?>
                <?= Html::a($entry->getLabel(), $entry->getUrl(), $entry->getHtmlOptions()); ?>
            <?php endif; ?>

            <?php if (!PoweredBy::isHidden() || array_key_last($entries) !== $k): ?>
                &nbsp;&middot;&nbsp;
            <?php endif; ?>

        <?php endforeach; ?>

        <?= PoweredBy::widget(); ?>
    </small>
</div>
<br/>
