<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<div class="panel panel-default">
    <?php if (!$type->isNewRecord) : ?>
        <div class="panel-heading"><?php echo Yii::t('AdminModule.views_space_editType', '<strong>Edit</strong> space type'); ?></div>
    <?php else: ?>
        <div class="panel-heading"><?php echo Yii::t('AdminModule.views_space_editType', '<strong>Create</strong> new space type'); ?></div>
    <?php endif; ?>
    <div class="panel-body">

        <?php
        $form = ActiveForm::begin([
                    'id' => 'login-form',
                    'options' => ['class' => 'form-horizontal'],
                ])
        ?>
        <?= $form->field($type, 'title') ?>
        <?= $form->field($type, 'item_title') ?>
        <?= $form->field($type, 'sort_key') ?>
        <?= $form->field($type, 'show_in_directory')->checkbox() ?>

        <div class="form-group">
            <div class="col-lg-offset-1 col-lg-11">
                <?= Html::submitButton(Yii::t('base', 'Save'), ['class' => 'btn btn-primary']) ?>

                <?php if ($canDelete): ?>
                    <?= Html::a(Yii::t('base', 'Delete'), Url::toRoute(['delete-type', 'id' => $type->id]), array('class' => 'btn btn-danger')); ?>
                <?php endif; ?>
            </div>
        </div>

        <?php ActiveForm::end() ?>

    </div>
</div>