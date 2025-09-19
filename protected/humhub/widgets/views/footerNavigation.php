<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\helpers\Html;
use humhub\modules\ui\menu\MenuLink;
use humhub\widgets\LanguageChooser;
use humhub\widgets\PoweredBy;

/* @var $this \humhub\components\View */
/* @var $entries MenuLink[] */
/* @var $options array */
/* @var $menu \humhub\widgets\FooterMenu */

?>

<div class="text-center footer-nav footer-nav-default">
    <small>
        <?php foreach ($entries as $k => $entry): ?>
            <?php if ($entry instanceof MenuLink): ?>
                <?= Html::a($entry->getLabel(), $entry->getUrl(), $entry->getHtmlOptions()); ?>

                <?php if (!PoweredBy::isHidden() || array_key_last($entries) !== $k): ?>
                    &nbsp;&middot;&nbsp;
                <?php endif; ?>
            <?php endif; ?>
        <?php endforeach; ?>

        <?= PoweredBy::widget() ?>

        <?= LanguageChooser::widget() ?>
    </small>
</div>
<br/>
