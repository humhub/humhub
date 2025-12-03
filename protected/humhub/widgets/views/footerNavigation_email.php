<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\helpers\Html;
use humhub\helpers\MailStyleHelper;
use humhub\modules\ui\menu\MenuLink;

/* @var $this \humhub\components\View */
/* @var $entries MenuLink[] */
/* @var $options array */
/* @var $menu \humhub\widgets\FooterMenu */

$i = 0;
?>

<center>
    <div class="text text-center powered footer-nav-email">
        <?php if (!empty($entries)): ?>
            <?php foreach ($entries as $k => $entry): ?>
                <?php if ($entry instanceof MenuLink): ?>
                    <?= Html::a($entry->getLabel(), $entry->getUrl(), $entry->getHtmlOptions([
                        'style' => 'text-decoration: none; color: ' . MailStyleHelper::getTextColorSoft2(),
                    ])) ?>

                    <?php if (array_key_last($entries) !== $k): ?>
                        &nbsp;&middot;&nbsp;
                    <?php endif; ?>
                <?php endif; ?>
            <?php endforeach; ?>
            <br/>
            <br/>
        <?php endif; ?>
    </div>
</center>
