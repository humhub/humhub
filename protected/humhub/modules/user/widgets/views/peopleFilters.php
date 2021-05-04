<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\libs\Html;
use humhub\modules\user\components\PeopleQuery;
use yii\helpers\Url;

/* @var $filters array */
?>

<?= Html::beginForm('/people', 'get', ['class' => 'form-search']); ?>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group form-group-search">
                <div class="form-search-field-info"><?= Yii::t('UserModule.base', 'Free text search in the directory (name, first name, telephone number, etc.)') ?></div>
                <?= Html::hiddenInput('page', '1'); ?>
                <?= Html::textInput('keyword', $filters['keyword'], ['class' => 'form-control form-search-filter', 'placeholder' => Yii::t('UserModule.base', 'search for members')]); ?>
                <?= Html::submitButton('<span class="fa fa-search"></span>', ['class' => 'form-button-search']); ?>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-search-field-info"><?= Yii::t('UserModule.base', 'Order') ?></div>
            <?= Html::dropDownList('order', $filters['order'], PeopleQuery::getOrderOptions(), ['data-action-change' => 'people.filterOrder', 'class' => 'form-control form-search-filter']); ?>
        </div>
        <div class="col-md-4 form-search-without-info">
            <?= Html::a(Yii::t('UserModule.base', 'Reset filter'), Url::to(['/people']), ['class' => 'form-search-reset']); ?>
        </div>
    </div>
<?= Html::endForm(); ?>