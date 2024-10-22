<?php

use humhub\compat\HForm;
use humhub\libs\Html;
use humhub\modules\ui\form\widgets\ActiveForm;

/* @var $hForm HForm */
/* @var $user \humhub\modules\user\models\User */
?>

<div class="clearfix">
    <div class="panel-body">
        <?= Html::backButton(['index'], ['label' => Yii::t('AdminModule.base', 'Back to overview'), 'class' => 'pull-right']); ?>
        <h4 class="pull-left"><?= Yii::t('AdminModule.user', 'Edit user: {name}', ['name' => Html::encode($user->displayName)]); ?></h4>
    </div>
</div>
<div class="panel-body">
    <?php $form = ActiveForm::begin(['options' => ['data-ui-widget' => 'ui.form.TabbedForm', 'data-ui-init' => '', 'style' => 'display:none']]); ?>
    <?= $hForm->render($form); ?>
    <?php ActiveForm::end(); ?>
</div>
