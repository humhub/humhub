<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use humhub\modules\space\models\Space;
use humhub\modules\space\modules\manage\widgets\DefaultMenu;

$this->registerJsFile('@web/resources/space/colorpicker/js/bootstrap-colorpicker-modified.js');
$this->registerCssFile('@web/resources/space/colorpicker/css/bootstrap-colorpicker.min.css');

?>

<?= DefaultMenu::widget(['space' => $model]); ?>
<br/>
<div class="panel panel-default">
    <div
        class="panel-heading"><?php echo Yii::t('SpaceModule.manage', '<strong>General</strong> settings'); ?></div>
    <div class="panel-body">
        <?php $form = ActiveForm::begin(); ?>

        <div class="row">
            <div
                class="col-md-8"> <?php echo $form->field($model, 'name')->textInput(['id' => 'space-name', 'placeholder' => Yii::t('SpaceModule.views_create_create', 'space name'), 'maxlength' => 45]); ?></div>
            <div class="col-md-4"><strong><?php echo Yii::t('SpaceModule.manage', 'Color'); ?></strong>

                <div class="input-group space-color-chooser-edit" style="margin-top: 5px;">

                    <?= Html::activeTextInput($model, 'color', ['class' => 'form-control', 'id' => 'space-color-picker-edit', 'value' => $model->color]); ?>
                    <span class="input-group-addon"><i></i></span>
                </div>
                <br></div>
        </div>

        <?php echo $form->field($model, 'description')->textarea(['rows' => 6]); ?>

        <?php echo $form->field($model, 'website')->textInput(['maxlength' => 45]); ?>

        <?php echo $form->field($model, 'tags')->textInput(['maxlength' => 200]); ?>

        <?php echo Html::submitButton(Yii::t('SpaceModule.views_admin_edit', 'Save'), array('class' => 'btn btn-primary')); ?>

        <?php echo \humhub\widgets\DataSaved::widget(); ?>

        <div class="pull-right">
            <?php if ($model->status == Space::STATUS_ENABLED) { ?>
                <?php echo Html::a(Yii::t('SpaceModule.views_admin_edit', 'Archive'), $model->createUrl('/space/manage/default/archive'), array('class' => 'btn btn-warning', 'data-post' => 'POST')); ?>
            <?php } elseif ($model->status == Space::STATUS_ARCHIVED) { ?>
                <?php echo Html::a(Yii::t('SpaceModule.views_admin_edit', 'Unarchive'), $model->createUrl('/space/manage/default/unarchive'), array('class' => 'btn btn-warning', 'data-post' => 'POST')); ?>
            <?php } ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

</div>

<script type="text/javascript">
    // prevent enter key and simulate ajax button submit click
    $(document).ready(function () {

        $('.space-color-chooser-edit').colorpicker({
            format: 'hex',
            color: '<?= $model->color; ?>',
            horizontal: false,
            component: '.input-group-addon',
            input: '#space-color-picker-edit',
        });
    });
</script>


