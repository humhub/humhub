<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>


<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.views_group_delete', '<strong>Delete</strong> space type'); ?></div>
    <div class="panel-body">

        <p>
            <?php echo Yii::t('AdminModule.views_group_delete', 'To delete the space type <strong>"{type}"</strong> you need to set an alternative type for existing spaces:', array('{type}' => Html::encode($type->title))); ?>
        </p>
        <?php
        $form = ActiveForm::begin([])
        ?>
        <?= $form->field($model, 'space_type_id')->dropDownList($alternativeTypes) ?>

        <div class="form-group">
            <div class="col-lg-offset-1 col-lg-11">
                <?= Html::submitButton(Yii::t('base', 'Delete'), ['class' => 'btn btn-danger']) ?>
            </div>
        </div>

        <?php ActiveForm::end() ?>        
    </div>
</div>




