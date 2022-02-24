<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\libs\Html;
use humhub\modules\ui\widgets\DirectoryFilters;
use yii\helpers\Url;

/* @var $directoryFilters DirectoryFilters */
?>

<?= Html::beginForm(Url::to([$directoryFilters->pageUrl]), 'get', ['class' => 'form-search']); ?>
    <?php if ($directoryFilters->paginationUsed) : ?>
        <?= Html::hiddenInput('page', '1'); ?>
    <?php endif; ?>
    <div class="row">
        <?= $directoryFilters->renderFilters() ?>
    </div>
<?= Html::endForm(); ?>