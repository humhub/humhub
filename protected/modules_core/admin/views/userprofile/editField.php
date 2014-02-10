<?php if (!$field->isNewRecord) : ?>
    <h1><?php echo Yii::t('AdminModule.base', 'Edit profile field'); ?></h1>
<?php else: ?>
    <h1><?php echo Yii::t('AdminModule.base', 'Create new profile field'); ?></h1>
<?php endif; ?>

<?php
echo $form;
?>


<script>

    /**
     * Switcher for Sub Forms (FormField Type)
     */

        // Hide all Subforms for types
    $(".fieldTypeSettings").hide();

    // Display only the current selected type form
    $("." + $("#ProfileField_field_type_class").val()).show();

    $("#ProfileField_field_type_class").on('change', function () {
        // Hide all Subforms for types
        $(".fieldTypeSettings").hide();

        // Show Current Selected
        $("." + $("#ProfileField_field_type_class").val()).show();
    });


</script>


