<?php

use humhub\libs\Html;
use humhub\modules\ui\form\widgets\ActiveForm;

/**
 * @var $model \humhub\modules\installer\forms\ConfigBasicForm
 */

?>
<div id="name-form" class="panel panel-default animated fadeIn">

    <div class="panel-heading">
        <?= Yii::t('InstallerModule.base', 'Social Network <strong>Name</strong>'); ?>
    </div>

    <div class="panel-body">
        <p><?= Yii::t('InstallerModule.base', 'Of course, your new social network needs a name. Please change the default name with one you like. (For example the name of your company, organization or club)'); ?></p>

        <?php $form = ActiveForm::begin(); ?>

        <div class="form-group">
            <?= $form->field($model, 'name')->textInput(); ?>
        </div>
        <hr>

        <?= Html::submitButton(Yii::t('InstallerModule.base', 'Next'), ['class' => 'btn btn-primary', 'data-ui-loader' => '']); ?>

        <?php $form::end(); ?>
    </div>
</div>

<script <?= Html::nonce() ?>>

    $(function () {
        // set cursor to email field
        $('#ConfigBasicForm_name').focus();
    });

    // Shake panel after wrong validation
    <?php if ($model->hasErrors()): ?>
    $('#name-form').removeClass('fadeIn');
    $('#name-form').addClass('shake');
    <?php endif; ?>

</script>


