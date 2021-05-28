<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\libs\Html;
use humhub\modules\space\widgets\SpacesFilters;
use yii\helpers\Url;

/* @var $spacesFilters SpacesFilters */
?>

<?= Html::beginForm(Url::to(['/space/spaces']), 'get', ['class' => 'form-search']); ?>
    <?= Html::hiddenInput('page', '1'); ?>
    <div class="row">
        <?= $spacesFilters->renderFilters() ?>
        <div class="col-md-2 form-search-without-info">
            <?= Html::a(Yii::t('UserModule.base', 'Reset filters'), Url::to(['/space/spaces']), ['class' => 'form-search-reset']); ?>
        </div>
    </div>
<?= Html::endForm(); ?>