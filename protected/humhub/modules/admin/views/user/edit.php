<?php

use humhub\libs\Html;
use yii\bootstrap5\ActiveForm;

/* @var $hForm \humhub\compat\HForm */
/* @var $user \humhub\modules\user\models\User */
?>

<div class="clearfix">
    <div class="card-body">
        <?= Html::backButton(['index'], ['label' => Yii::t('AdminModule.base', 'Back to overview'), 'class' => 'float-end']); ?>
        <h4 class="float-start"><?= Yii::t('AdminModule.user', 'Edit user: {name}', ['name' => Html::encode($user->displayName)]); ?></h4>
    </div>
</div>
<div class="card-body">
    <?php $form = ActiveForm::begin(['options' => ['data-ui-widget' => 'ui.form.TabbedForm', 'data-ui-init' => '', 'style' => 'display:none']]); ?>
        <?= $hForm->render($form); ?>
    <?php ActiveForm::end(); ?>
</div>
