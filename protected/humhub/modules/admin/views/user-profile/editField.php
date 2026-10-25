<?php

use humhub\compat\HForm;
use humhub\helpers\Html;
use humhub\modules\user\models\ProfileField;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\ContentCard;
use humhub\widgets\form\ActiveForm;

/* @var $field ProfileField */
/* @var $hForm HForm */
?>

<?php ContentCard::begin([
    'title' => $field->isNewRecord
        ? Yii::t('AdminModule.user', 'Create new profile field')
        : Yii::t('AdminModule.user', 'Edit profile field'),
    'buttons' => Button::back(['index'], Yii::t('AdminModule.base', 'Back to overview'))->sm()->asString(),
    'options' => ['id' => 'edit-profile-field-root'],
]) ?>
<?php $form = ActiveForm::begin(); ?>
<?= $hForm->render($form); ?>
<?php ActiveForm::end(); ?>
<?php ContentCard::end() ?>

<script <?= Html::nonce() ?>>

    /**
     * Switcher for Sub Forms (FormField Type)
     */
    var checkFieldTypeFormState = function () {
        $("#edit-profile-field-root .mb-3").show();

        // Hide all form type specific forms
        $(".fieldTypeSettings").hide();

        var $fieldTypeSelect = $("#profilefield-field_type_class");
        var fieldTypeClass = $fieldTypeSelect.val();
        var showTypeSettings = fieldTypeClass.replace(/[\\]/g, '_');

        // Display only the current selected type form
        $("." + showTypeSettings).show();

        var $selectedOption = $fieldTypeSelect.find(':selected');
        var hideFields = $selectedOption.data('hiddenFields');

        hideFields.forEach(function (value) {
            $('.field-profilefield-' + value).hide();
        })
    };

    checkFieldTypeFormState();

    $("#profilefield-field_type_class").on('change', function () {
        checkFieldTypeFormState();
    });

</script>
