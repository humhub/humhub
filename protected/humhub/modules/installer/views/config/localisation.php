<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\libs\Html;
use humhub\modules\installer\forms\LocalisationForm;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\widgets\Button;

/* @var LocalisationForm $model */
?>
<div id="localisation" class="panel panel-default animated fadeIn">

    <div class="panel-heading">
        <strong><?= Yii::t('InstallerModule.base', 'Localisation') ?></strong>
    </div>

    <div class="panel-body">
        <p><?= Yii::t('InstallerModule.base', 'Language and timezone are automatically set according to the user\'s browser settings. If that is not possible, the default settings are used.') ?></p>

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
<script <?= Html::nonce() ?>>
    $('#localisationform-timezone').val(Intl.DateTimeFormat().resolvedOptions().timeZone);
</script>
