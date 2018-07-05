<?php

use humhub\libs\Html;

humhub\assets\TabbedFormAsset::register($this);
?>

<div class="panel-body">
    <div class="clearfix">

        <div class="pull-right">
            <?= Html::backButton(['index'], ['label' => Yii::t('AdminModule.base', 'Back to overview')]); ?>
            <?= Html::a('<i class="fa fa-paper-plane" aria-hidden="true"></i>&nbsp;&nbsp;' . Yii::t('AdminModule.views_user_index', 'Send invite'), ['/user/invite'], ['class' => 'btn btn-success', 'data-target' => '#globalModal']); ?>
        </div>
        
        <h4 class="pull-left"><?= Yii::t('AdminModule.views_user_index', 'Add new user'); ?></h4>
    </div>
    <br>
    <?php $form = \yii\widgets\ActiveForm::begin(['options' => ['data-ui-widget' => 'ui.form.TabbedForm', 'data-ui-init' => '']]); ?>
    <?= $hForm->render($form); ?>
    <?php \yii\widgets\ActiveForm::end(); ?>
</div>
