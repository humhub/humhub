<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\installer\forms\LocalisationForm;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\widgets\Button;

/* @var LocalisationForm $model */
?>
<div id="localisation" class="panel panel-default animated fadeIn">

    <div class="panel-heading">
        <?= Yii::t('InstallerModule.base', '<strong>Localisation</strong> Settings') ?>
    </div>

    <div class="panel-body">
        <p><?= Yii::t('InstallerModule.base', 'Your new social network requires a few localization settings. Please update the default timezone and language.') ?></p>

        <?php $form = ActiveForm::begin() ?>

        <?php if ($model->hasLanguages()) : ?>
            <?= $languageDropDown = $form->field($model, 'language')->dropDownList($model->getLanguageOptions()) ?>
        <?php endif; ?>

        <?= $form->field($model, 'timeZone')->dropDownList($model->getTimeZoneOptions()) ?>

        <hr>

        <?= Button::primary(Yii::t('InstallerModule.base', 'Next'))
            ->submit()
            ->loader(false) ?>

        <?php $form::end() ?>
    </div>
</div>
