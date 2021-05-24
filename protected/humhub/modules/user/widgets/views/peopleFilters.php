<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\libs\Html;
use humhub\modules\admin\models\forms\PeopleSettingsForm;
use humhub\modules\user\models\ProfileField;
use humhub\modules\user\widgets\PeopleFilters;
use yii\helpers\Url;

/* @var $groupOptions array */
/* @var $connectionOptions array */
/* @var $profileFields ProfileField[] */
?>

<?= Html::beginForm(Url::to(['/user/people']), 'get', ['class' => 'form-search']); ?>
    <?= Html::hiddenInput('page', '1'); ?>
    <div class="row">
        <div class="col-md-6 form-search-filter-keyword">
            <div class="form-search-field-info"><?= Yii::t('UserModule.base', 'Free text search in the directory (name, first name, telephone number, etc.)') ?></div>
            <?= Html::textInput('keyword', PeopleFilters::getValue('keyword'), ['class' => 'form-control form-search-filter', 'placeholder' => Yii::t('UserModule.base', 'search for members')]); ?>
            <?= Html::submitButton('<span class="fa fa-search"></span>', ['class' => 'form-button-search']); ?>
        </div>
        <?php if (count($groupOptions)) : ?>
        <div class="col-md-2">
            <div class="form-search-field-info"><?= Yii::t('UserModule.base', 'Group') ?></div>
            <?= Html::dropDownList('groupId', PeopleFilters::getValue('groupId'), $groupOptions, ['data-action-change' => 'people.applyFilters', 'class' => 'form-control form-search-filter']); ?>
        </div>
        <?php endif; ?>
        <div class="col-md-2">
            <div class="form-search-field-info"><?= Yii::t('UserModule.base', 'Sorting') ?></div>
            <?= Html::dropDownList('sort', PeopleFilters::getValue('sort'), PeopleSettingsForm::getSortingOptions(), ['data-action-change' => 'people.applyFilters', 'class' => 'form-control form-search-filter']); ?>
        </div>
        <div class="col-md-2">
            <div class="form-search-field-info"><?= Yii::t('UserModule.base', 'Connection') ?></div>
            <?= Html::dropDownList('connection', PeopleFilters::getValue('connection'), $connectionOptions, ['data-action-change' => 'people.applyFilters', 'class' => 'form-control form-search-filter']); ?>
        </div>
        <?php foreach ($profileFields as $profileField) : ?>
        <div class="col-md-2">
            <div class="form-search-field-info"><?= Yii::t($profileField->getTranslationCategory(), $profileField->title) ?></div>
            <?= PeopleFilters::renderProfileFieldFilter($profileField) ?>
        </div>
        <?php endforeach; ?>
        <div class="col-md-2 form-search-without-info">
            <?= Html::a(Yii::t('UserModule.base', 'Reset filters'), Url::to(['/user/people']), ['class' => 'form-search-reset']); ?>
        </div>
    </div>
<?= Html::endForm(); ?>