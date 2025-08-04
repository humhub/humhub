<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\helpers\Html;
use humhub\modules\ui\widgets\DirectoryFilters;
use yii\helpers\Url;

/* @var $directoryFilters DirectoryFilters */
/* @var $options array */
?>

<?= Html::beginForm(Url::to([$directoryFilters->pageUrl]), 'get', $options); ?>
<?php if ($directoryFilters->paginationUsed) : ?>
    <?= Html::hiddenInput('page', '1'); ?>
<?php endif; ?>
<div class="row">
    <?= $directoryFilters->renderFilters() ?>
</div>
<?= Html::endForm(); ?>
