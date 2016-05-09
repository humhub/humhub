<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.views_user_index', '<strong>Manage</strong> users'); ?></div>
    <?= \humhub\modules\admin\widgets\UserMenu::widget(); ?>
    <div class="panel-body">
        
        <p />

        <?php $form = \yii\widgets\ActiveForm::begin(); ?>
        <?php echo $hForm->render($form); ?>
        <?php \yii\widgets\ActiveForm::end(); ?>

    </div>
</div>