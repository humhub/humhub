<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use yii\helpers\Html;
use humhub\widgets\PoweredBy;
use humhub\modules\ui\menu\MenuLink;

/* @var $this \humhub\modules\ui\view\components\View */
/* @var $entries MenuLink[] */
/* @var $options array */
/* @var $menu \humhub\widgets\FooterMenu */

?>

<div class="text text-center powered">
    <?php if (!empty($entries)): ?>
        <div class="footer-nav footer-nav-login">
            <?php foreach ($entries as $k => $entry): ?>
                <?php if ($entry instanceof MenuLink): ?>
                    <?= Html::a($entry->getLabel(), $entry->getUrl(), $entry->getHtmlOptions(['data-pjax-prevent' => true])); ?>
                <?php endif; ?>

                <?php if (array_key_last($entries) !== $k): ?>
                    &nbsp;&middot;&nbsp;
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <br/>
    <?= PoweredBy::widget(); ?>
</div>
