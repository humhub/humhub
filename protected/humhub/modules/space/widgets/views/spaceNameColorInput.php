<?php
    use yii\helpers\Html;
    
    $this->registerJsFile('@web/resources/space/colorpicker/js/bootstrap-colorpicker-modified.js', ['position'=>  yii\web\View::POS_BEGIN, 'depends' => [\yii\bootstrap\BootstrapPluginAsset::className()]]);
    $this->registerCssFile('@web/resources/space/colorpicker/css/bootstrap-colorpicker.min.css', ['position'=>  yii\web\View::POS_BEGIN, 'depends' => [\yii\bootstrap\BootstrapPluginAsset::className()]]);
    
    $ts = time();
    $inputId = $ts.'space-color-picker-edit';
    $containerId = $ts.'space-color-chooser-edit';
    $addonClass = $ts.'input-group-addon';
    
    if($model->color == null) {
        $model->color = '#d1d1d1';
    }
?>

<div id="<?= $containerId ?>" class="form-group space-color-chooser-edit" style="margin-top: 5px;">
    <?= Html::activeTextInput($model, 'color', ['class' => 'form-control', 'id' => $inputId, 'value' => $model->color, 'style' => 'display:none']); ?>
    <?= $form->field($model, 'name', ['template' => '
        {label}
        <div class="input-group">
            <span class="input-group-addon">
                <i></i>
            </span>
            {input}
        </div>
        {error}{hint}'
        ])->textInput(['placeholder' => Yii::t('SpaceModule.views_create_create', 'Space name'), 'maxlength' => 45 ]) ?>
</div>
<script type="text/javascript">
    // prevent enter key and simulate ajax button submit click
    $(document).ready(function () {
        $container = $('#<?= $containerId ?>');
        $container.colorpicker({
            format: 'hex',
            color: '<?= $model->color ?>',
            'align': 'left',
            horizontal: false,
            component: '.input-group-addon',
            input: '#<?= $inputId ?>'
        });

        //Add hex input field to color picker
        $container.on('create', function() {
            
            var picker = $(this).data('colorpicker');
            picker.picker.css('z-index', '3000');
            if(!picker.picker.find('.hexInput').length) {

                var $colorPickerHexInput = $('<input type="text" class="hexInput" style="border:0px;outline: none;width:120px;" value="'+picker.color.toHex()+'"></input>');
                picker.picker.append($colorPickerHexInput);
                $colorPickerHexInput.on('change', function() {
                    picker.color.setColor($(this).val());
                    picker.update();
                });
                
                
                $colorPickerHexInput.on('click', function(event) {
                    $colorPickerHexInput.focus();
                    //$colorPickerHexInput.select();
                    event.stopPropagation(); 
                    event.preventDefault();
                });
                
                $colorPickerHexInput.on('keydown', function(e) { 
                    var keyCode = e.keyCode || e.which; 
                    //Close On Tab
                    if (keyCode === 9) { 
                      e.preventDefault(); 
                      picker.hide();
                      $('#space-name').focus();
                    } 
              });
            }
        });
        
        $container.on('showPicker', function() {
            $(this).data('colorpicker').picker.find('.hexInput').select();
        });
        
        $container.on('changeColor', function() {
            var picker = $(this).data('colorpicker');
            picker.picker.find('.hexInput').val(picker.color.toHex());
        });
    });
</script>