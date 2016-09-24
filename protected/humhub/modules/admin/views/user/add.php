<?php

use yii\helpers\Html;
use yii\helpers\Url;

humhub\assets\TabbedFormAsset::register($this);
?>

<div class="panel-body">
    <div class="clearfix">
        <?php echo Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i>&nbsp;&nbsp;' . Yii::t('AdminModule.user', 'Back to overview'), Url::to(['index']), array('class' => 'btn btn-default pull-right'));
        ?>
        <h4 class="pull-left"><?php echo Yii::t('AdminModule.views_user_index', 'Add new user'); ?></h4>
    </div>
    <br />
    <?php $form = \yii\widgets\ActiveForm::begin(['options' => ['data-ui-tabbed-form' => '']]); ?>
    <?php echo $hForm->render($form); ?>
    <?php \yii\widgets\ActiveForm::end(); ?>
</div>
