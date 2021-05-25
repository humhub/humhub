<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\libs\Html;
use humhub\modules\space\widgets\SpacesFilters;
use yii\helpers\Url;
?>

<?= Html::beginForm(Url::to(['/space/spaces']), 'get', ['class' => 'form-search']); ?>
    <?= Html::hiddenInput('page', '1'); ?>
    <div class="row">
        <div class="col-md-6 form-search-filter-keyword">
            <div class="form-search-field-info"><?= Yii::t('SpaceModule.base', 'Free text search in the directory (name, description, tags, etc.)') ?></div>
            <?= Html::textInput('keyword', SpacesFilters::getValue('keyword'), ['class' => 'form-control form-search-filter', 'placeholder' => Yii::t('SpaceModule.base', 'search for spaces')]); ?>
            <?= Html::submitButton('<span class="fa fa-search"></span>', ['class' => 'form-button-search']); ?>
        </div>
        <div class="col-md-2">
            <div class="form-search-field-info"><?= Yii::t('SpaceModule.base', 'Sorting') ?></div>
            <?= Html::dropDownList('sort', SpacesFilters::getValue('sort'), SpacesFilters::getSortingOptions(), ['data-action-change' => 'directory.applyFilters', 'class' => 'form-control form-search-filter']); ?>
        </div>
        <div class="col-md-2">
            <div class="form-search-field-info"><?= Yii::t('SpaceModule.base', 'Connection') ?></div>
            <?= Html::dropDownList('connection', SpacesFilters::getValue('connection'), SpacesFilters::getConnectionOptions(), ['data-action-change' => 'directory.applyFilters', 'class' => 'form-control form-search-filter']); ?>
        </div>
        <div class="col-md-2 form-search-without-info">
            <?= Html::a(Yii::t('UserModule.base', 'Reset filters'), Url::to(['/space/spaces']), ['class' => 'form-search-reset']); ?>
        </div>
    </div>
<?= Html::endForm(); ?>