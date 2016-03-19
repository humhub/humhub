<?php

humhub\assets\Select2Asset::register($this);

?>
<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.views_user_edit', '<strong>Edit</strong> user'); ?></div>
    <div class="panel-body">
        <?php $form = \yii\widgets\ActiveForm::begin(); ?>
        <?php echo $hForm->render($form); ?>
        <?php \yii\widgets\ActiveForm::end(); ?>
    </div>
    <script type="text/javascript">
        $.fn.select2.defaults = {};
        
        //This function manipulates the presentation of the dropdown results
        //To fit the user picker style
        var updateDropDown = function() {
            $('span[role="presentation"]').hide();
            $('.select2-selection__choice').addClass('userInput');
            $closeButton = $('<i class="fa fa-times-circle"></i>');
            $closeButton.on('click', function() {
                $(this).siblings('span[role="presentation"]').trigger('click');
            });
            $('.select2-selection__choice').append($closeButton);
        };
        
        var isOpen = false; 
        
        //We have to overwrite the the result gui after every change
        $('.multiselect_dropdown').select2({}).on('change', function() {
            updateDropDown();
        }).on('select2:open', function() {
            isOpen = true;
        }).on('select2:close', function() {
            isOpen = false;
        });
        
        //For highlighting the input
        $(".select2-container").on("focusin", function () { 
            $(this).find('.select2-selection').addClass('select2-selection--focus');
        });
       
        //Since the focusout of the ontainer is called when the dropdown is opened we have to use this focusout
        $(document).on('focusout','.select2-search__field', function() {
            if(!isOpen) {
                $(this).closest('.select2-selection').removeClass('select2-selection--focus');
            }
        });
        
        updateDropDown();
        
    </script>
</div>