<?php

use humhub\compat\CActiveForm;
use humhub\compat\CHtml;
use humhub\models\Setting;
use yii\helpers\Url;
?>
<div class="panel panel-default">
    <div
        class="panel-heading"><?php echo Yii::t('AdminModule.views_setting_security', '<strong>Security</strong> settings and roles'); ?></div>
    <div class="panel-body">

        <?php $form = CActiveForm::begin(); ?>

        <?php echo $form->errorSummary($model); ?>


        <?php echo $form->checkBox($model, 'canAdminAlwaysDeleteContent'); ?>

        <?php echo \humhub\widgets\DataSaved::widget(); ?>
        <?php CActiveForm::end(); ?>

    </div>
</div>








