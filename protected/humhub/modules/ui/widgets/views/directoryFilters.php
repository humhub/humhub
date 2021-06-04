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
    <?= Html::hiddenInput('page', '1'); ?>
    <div class="row">
        <?= $directoryFilters->renderFilters() ?>
        <div class="col-md-2 form-search-without-info">
            <?= Html::a(Yii::t('UiModule.base', 'Reset filters'), Url::to([$directoryFilters->pageUrl]), ['class' => 'form-search-reset']); ?>
        </div>
    </div>
<?= Html::endForm(); ?>