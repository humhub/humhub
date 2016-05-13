<div class="panel-body">
<h4><?php echo Yii::t('AdminModule.views_user_index', 'Add new user'); ?></h4>
<p />

<?php $form = \yii\widgets\ActiveForm::begin(); ?>
<?php echo $hForm->render($form); ?>
<?php \yii\widgets\ActiveForm::end(); ?>
</div>
