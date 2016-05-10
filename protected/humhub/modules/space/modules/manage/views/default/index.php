<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use humhub\modules\space\models\Space;
use humhub\modules\space\modules\manage\widgets\DefaultMenu;

$this->registerJsFile('@web/resources/space/colorpicker/js/bootstrap-colorpicker-modified.js');
$this->registerCssFile('@web/resources/space/colorpicker/css/bootstrap-colorpicker.min.css');
?>


<div class="panel panel-default">

    <div>
        <div class="panel-heading">
            <?php echo Yii::t('SpaceModule.views_settings', '<strong>Space</strong> settings'); ?>
        </div>
    </div>

    <?= DefaultMenu::widget(['space' => $model]); ?>
    <div class="panel-body">

        <?php $form = ActiveForm::begin(['options' => ['id' => 'spaceIndexForm'], 'enableClientValidation' => false]); ?>
        <div class="form-group space-color-chooser-edit" style="margin-top: 5px;">
            <?= Html::activeTextInput($model, 'color', ['class' => 'form-control', 'id' => 'space-color-picker-edit', 'value' => $model->color, 'style' => 'display:none']); ?>
            <?= $form->field($model, 'name', [ 'template' => '
                            {label}
                            <div class="input-group">
                            <span class="input-group-addon">
                              <i></i>
                            </span>
                           {input}
                           </input>
                        </div>
                        {error}{hint}'])->textInput() ?>
        </div>


        <?php echo $form->field($model, 'description')->textarea(['rows' => 6]); ?>

        <div class="row">
            <div class="col-md-9">
                <?php echo $form->field($model, 'tags')->textInput(['maxlength' => 200]); ?>
            </div>
        </div>


        <?php echo Html::submitButton(Yii::t('SpaceModule.views_admin_edit', 'Save'), array('class' => 'btn btn-primary', 'data-ui-loader' => '')); ?>

        <?php echo \humhub\widgets\DataSaved::widget(); ?>

        <div class="pull-right">
            <?php if ($model->isSpaceOwner()) : ?>
                <?php echo Html::a(Yii::t('SpaceModule.views_admin_edit', 'Delete'), $model->createUrl('delete'), array('class' => 'btn btn-danger', 'data-post' => 'POST')); ?>
            <?php endif; ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

</div>

<script type="text/javascript">
    // prevent enter key and simulate ajax button submit click
    $(document).ready(function () {
        var $colorPickerHexInput, picker;

        $('.space-color-chooser-edit').colorpicker({
            format: 'hex',
            color: '<?= $model->color; ?>',
            'align': 'left',
            horizontal: false,
            component: '.input-group-addon',
            input: '#space-color-picker-edit',
        });

        //Add hex input field to color picker
        $('.space-color-chooser-edit').on('create', function () {
            if (typeof $colorPickerHexInput === 'undefined') {
                picker = $(this).data('colorpicker');
                $colorPickerHexInput = $('<input type="text" style="border:0px;outline: none;" id="colorPickerHexInput" value="' + picker.color.toHex() + '"></input>');
                picker.picker.append($colorPickerHexInput);
                $colorPickerHexInput.on('change', function () {
                    picker.color.setColor($(this).val());
                    picker.update();
                });

                $colorPickerHexInput.on('keydown', function (e) {
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

        $('.colorpicker').on('click', function () {
            $colorPickerHexInput.select();
        });

        $('.space-color-chooser-edit').on('showPicker', function () {
            $colorPickerHexInput.select();
        });

        $('.space-color-chooser-edit').on('changeColor', function () {
            $colorPickerHexInput.val(picker.color.toHex());
        });
    });
</script>


