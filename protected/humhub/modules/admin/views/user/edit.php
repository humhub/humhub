<?php

use humhub\libs\Html;
use yii\bootstrap\ActiveForm;

humhub\assets\TabbedFormAsset::register($this);
?>

<div class="clearfix">
    <div class="panel-body">
        <?= Html::backButton(['index'], ['label' => Yii::t('AdminModule.base', 'Back to overview'), 'class' => 'pull-right']); ?>
        <h4 class="pull-left"><?= Yii::t('AdminModule.views_user_edit', 'Edit user: {name}', ['name' => $user->displayName]); ?></h4>
    </div>
</div>
<div class="panel-body">
    <?php $form = ActiveForm::begin(['options' => ['data-ui-widget' => 'ui.form.TabbedForm', 'data-ui-init' => '', 'style' => 'display:none']]); ?>
    <?= $hForm->render($form); ?>
    <?php ActiveForm::end(); ?>
</div>
