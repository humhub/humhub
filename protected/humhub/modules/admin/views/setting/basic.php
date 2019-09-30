<?php

use humhub\libs\TimezoneHelper;
use yii\widgets\ActiveForm;
use humhub\compat\CHtml;
?>

<div class="panel-body">
    <h4><?= Yii::t('AdminModule.settings', 'General Settings'); ?></h4>
    <div class="help-block">
        <?= Yii::t('AdminModule.settings', 'Here you can configure basic settings of your social network.'); ?>
    </div>

    <br>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name'); ?>

    <?= $form->field($model, 'baseUrl'); ?>
    <p class="help-block"><?= Yii::t('AdminModule.settings', 'E.g. http://example.com/humhub'); ?></p>

    <?php $allowedLanguages = Yii::$app->i18n->getAllowedLanguages(); ?>
    <?php if (count($allowedLanguages) > 1) : ?>
        <?= $languageDropDown = $form->field($model, 'defaultLanguage')->dropDownList($allowedLanguages, ['data-ui-select2' => '']); ?>
    <?php endif; ?>

    <?= $form->field($model, 'timeZone')->dropDownList(TimezoneHelper::generateList(true), ['data-ui-select2' => '']); ?>

    <?= $form->field($model, 'defaultStreamSort')->dropDownList($model->getDefaultStreamSortOptions()); ?>

    <strong><?= Yii::t('AdminModule.settings', 'Dashboard'); ?></strong>
    <br>
    <br>
    <?= $form->field($model, 'tour')->checkbox(); ?>
    <?= $form->field($model, 'dashboardShowProfilePostForm')->checkbox(); ?>

    <strong><?= Yii::t('AdminModule.settings', 'Friendship'); ?></strong>
    <br>
    <br>
    <?= $form->field($model, 'enableFriendshipModule')->checkbox(); ?>

    <hr>

    <?= CHtml::submitButton(Yii::t('AdminModule.settings', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => ""]); ?>

    <!-- show flash message after saving -->
    <?php \humhub\widgets\DataSaved::widget(); ?>

    <?php ActiveForm::end(); ?>
</div>
