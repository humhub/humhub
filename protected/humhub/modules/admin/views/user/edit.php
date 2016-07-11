<?php

use yii\helpers\Html;
use yii\helpers\Url;

humhub\assets\TabbedFormAsset::register($this);

humhub\assets\Select2ExtensionAsset::register($this);
?>

<div class="clearfix">
    <div class="panel-body">
        <?php echo Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i>&nbsp;&nbsp;' . Yii::t('AdminModule.user', 'Back to overview'), 
                Url::to(['index']), array('class' => 'btn btn-default pull-right')); ?>
        <h4 class="pull-left"><?= Yii::t('AdminModule.views_user_edit', 'Edit user: {name}', ['name' => $user->displayName]); ?></h4>
  
    </div>
</div>
<div class="panel-body">
    <?php $form = \yii\widgets\ActiveForm::begin(['options' => ['data-ui-tabbed-form' => '']]); ?>
    <?php echo $hForm->render($form); ?>
    <?php \yii\widgets\ActiveForm::end(); ?>
</div>
