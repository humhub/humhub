<?php

use humhub\modules\installer\controllers\ConfigController;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
?>
<div id="name-form" class="panel panel-default animated fadeIn">

    <div class="panel-heading">
        <?php echo Yii::t('InstallerModule.base', 'Use <strong>Case</strong>'); ?>
    </div>

    <div class="panel-body">

        <p><?php echo Yii::t('InstallerModule.base', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.'); ?></p>


        <?php $form = ActiveForm::begin(); ?>

        <?=
        $form->field($model, 'useCase')->radioList([
            ConfigController::USECASE_SOCIAL_COLLABORATION => 'Social Collaboration',
            ConfigController::USECASE_SOCIAL_INTRANET => 'Social Intranet',
            ConfigController::USECASE_EDUCATION => 'Education (e.g. for Schools or Universities)',
            ConfigController::USECASE_COMMUNITY => 'Commmunity',
            ConfigController::USECASE_OTHER => 'Other',
        ]);
        ?>

        <hr>

        <?php echo Html::submitButton(Yii::t('base', 'Next'), array('class' => 'btn btn-primary')); ?>

<?php ActiveForm::end(); ?>
    </div>
</div>


