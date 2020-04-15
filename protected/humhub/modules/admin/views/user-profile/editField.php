<?php

use humhub\compat\HForm;
use humhub\libs\Html;
use humhub\modules\user\models\ProfileField;
use yii\widgets\ActiveForm;

/* @var $field ProfileField */
/* @var $hForm HForm */
?>

<div class="panel-body">
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

    // Hide all Subforms for types
    $(".fieldTypeSettings").hide();

    showTypeSettings = $("#profilefield-field_type_class").val();
    showTypeSettings = showTypeSettings.replace(/[\\]/g, '_');

    // Display only the current selected type form
    $("." + showTypeSettings).show();

    $("#profilefield-field_type_class").on('change', function () {
        // Hide all Subforms for types
        $(".fieldTypeSettings").hide();

        // Show Current Selected
        showTypeSettings = $("#profilefield-field_type_class").val();
        showTypeSettings = showTypeSettings.replace(/[\\]/g, '_');

        $("." + showTypeSettings).show();
    });

</script>
