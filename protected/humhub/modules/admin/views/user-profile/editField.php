<?php

use humhub\libs\Html;
use yii\helpers\Url;
?>

<div class="panel-body">
    <div class="pull-right">
        <?= Html::backButton(['index'], ['label' => Yii::t('AdminModule.base', 'Back to overview'), 'class' => 'pull-right']); ?>
    </div>

    <?php if (!$field->isNewRecord) : ?>
        <h4><?= Yii::t('AdminModule.views_userprofile_editField', 'Edit profile field'); ?></h4>
    <?php else: ?>
        <h4><?= Yii::t('AdminModule.views_userprofile_editField', 'Create new profile field'); ?></h4>
    <?php endif; ?>

    <br>

    <?php $form = \yii\widgets\ActiveForm::begin(); ?>
    <?= $hForm->render($form); ?>
    <?php \yii\widgets\ActiveForm::end(); ?>
</div>

<script>

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