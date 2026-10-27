<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\View;
use humhub\helpers\Html;

/* @var $this View */
/* @var $id string */
/* @var $title string */
/* @var $titleTag string */
/* @var $actions string */
/* @var $menu string */
/* @var $tabs string */
/* @var $backUrl string|array|null */
/* @var $backLabel string|null */

$titleId = $id . '-title';
?>
<section class="page-header" aria-labelledby="<?= $titleId ?>">
    <div class="page-header-bar">
        <?php if ($backUrl !== null) : ?>
            <?= $this->render('_backLink', ['url' => $backUrl, 'label' => $backLabel, 'class' => 'page-header-back']) ?>
        <?php endif; ?>
        <?= Html::tag($titleTag, Html::encode($title), ['id' => $titleId, 'class' => 'page-header-title']) ?>
        <?php if ($actions !== '' || $menu !== '') : ?>
            <div class="page-header-actions">
                <?= $actions ?>
                <?= $menu ?>
            </div>
        <?php endif; ?>
    </div>
    <?php if ($tabs !== '') : ?>
        <div class="page-header-body">
            <?= $tabs ?>
        </div>
    <?php endif; ?>
</section>
