<?php

use humhub\compat\HForm;
use humhub\libs\Html;
use humhub\modules\user\models\ProfileField;
use yii\widgets\ActiveForm;

/* @var $field ProfileField */
/* @var $hForm HForm */
?>

<div id="edit-profile-field-root" class="panel-body">
    <div class="pull-right">
        <?= Html::backButton(['index'], ['label' => Yii::t('AdminModule.base', 'Back to overview'), 'class' => 'pull-right']); ?>
    </div>

    <?php if (!$field->isNewRecord): ?>
        <h4><?= Yii::t('AdminModule.user', 'Edit profile field') ?></h4>
    <?php else: ?>
        <h4><?= Yii::t('AdminModule.user', 'Create new profile field') ?></h4>
    <?php endif; ?>

    <br>

    <?php $form = ActiveForm::begin(); ?>
        <?= $hForm->render($form); ?>
    <?php ActiveForm::end(); ?>
</div>

<script <?= Html::nonce() ?>>

    /**
     * Switcher for Sub Forms (FormField Type)
     */
    var checkFieldTypeFormState = function()
    {
        $("#edit-profile-field-root .form-group").show();

        // Hide all form type specific forms
        $(".fieldTypeSettings").hide();

        var $fieldTypeSelect =  $("#profilefield-field_type_class");
        var fieldTypeClass = $fieldTypeSelect.val();
        var showTypeSettings = fieldTypeClass.replace(/[\\]/g, '_');

        // Display only the current selected type form
        $("." + showTypeSettings).show();

        var $selectedOption = $fieldTypeSelect.find(':selected');
        var hideFields = $selectedOption.data('hiddenFields');

        hideFields.forEach(function(value) {
            $('.field-profilefield-'+value).hide();
        })
    };

    checkFieldTypeFormState();

    $("#profilefield-field_type_class").on('change', function () {
        checkFieldTypeFormState();
    });

</script>
