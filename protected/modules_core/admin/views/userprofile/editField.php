<div class="panel panel-default">

    <?php if (!$field->isNewRecord) : ?>
        <div class="panel-heading"><?php echo Yii::t('AdminModule.views_userprofile_editField', '<strong>Edit</strong> profile field'); ?></div>
    <?php else: ?>
        <div class="panel-heading"><?php echo Yii::t('AdminModule.views_userprofile_editField', '<strong>Create</strong> new profile field'); ?></div>
    <?php endif; ?>

    <div class="panel-body">


        <?php
        echo $form;
        ?>

    </div>
</div>


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


