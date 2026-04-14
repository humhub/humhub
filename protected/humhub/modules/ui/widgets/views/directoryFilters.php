<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\helpers\Html;
use humhub\helpers\ThemeHelper;
use humhub\modules\ui\widgets\DirectoryFilters;
use yii\helpers\Url;

/* @var $directoryFilters DirectoryFilters */
/* @var $options array */
?>

<?= Html::beginForm(Url::to([$directoryFilters->pageUrl]), 'get', $options); ?>
<?php if ($directoryFilters->paginationUsed) : ?>
    <?= Html::hiddenInput('page', '1'); ?>
<?php endif; ?>
<div class="container<?= ThemeHelper::isFluid() ? '-fluid' : '' ?> g-0 overflow-x-hidden">
    <div class="row gy-2">
        <?= $directoryFilters->renderFilters() ?>
    </div>
</div>
<?= Html::endForm(); ?>
