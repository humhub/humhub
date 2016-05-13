<?php 
    humhub\assets\TabbedFormAsset::register($this);
?>

<div class="panel-heading">
    <?php echo Yii::t('UserModule.views_account_edit', '<strong>User</strong> details'); ?>

    <!-- show flash message after saving -->
    <?php echo \humhub\widgets\DataSaved::widget(); ?>
</div>
<div class="panel-body">
    <?php $form = \yii\widgets\ActiveForm::begin(['enableClientValidation' => false, 'options' => ['data-ui-tabbed-form' => '']]); ?>
    <?php echo $hForm->render($form); ?>
    <?php \yii\widgets\ActiveForm::end(); ?>
</div>