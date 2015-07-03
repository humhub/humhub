<div class="panel panel-default">

    <?php if (!$field->isNewRecord) : ?>
        <div class="panel-heading"><?php echo Yii::t('AdminModule.views_userprofile_editField', '<strong>Edit</strong> profile field'); ?></div>
    <?php else: ?>
        <div class="panel-heading"><?php echo Yii::t('AdminModule.views_userprofile_editField', '<strong>Create</strong> new profile field'); ?></div>
    <?php endif; ?>

    <div class="panel-body">

        <?php $form = \yii\widgets\ActiveForm::begin(); ?>
        <?php echo $hForm->render($form); ?>
        <?php \yii\widgets\ActiveForm::end(); ?>

    </div>
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
        console.log(showTypeSettings);
        $("." + showTypeSettings).show();
    });


</script>


