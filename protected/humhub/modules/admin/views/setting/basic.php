<?php

use humhub\helpers\Html;
use humhub\modules\admin\models\forms\BasicSettingsForm;
use humhub\widgets\bootstrap\Alert;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\ContentCard;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\Icon;

/* @var BasicSettingsForm $model */
?>
<?php ContentCard::begin([
    'title' => Yii::t('AdminModule.base', 'General'),
    'buttons' => Html::beginForm(['clear-cache'])
        . Button::secondary(Yii::t('AdminModule.settings', 'Flush Caches'))->submit()->sm()
        . Html::endForm(),
]) ?>
<?php $form = ActiveForm::begin(['acknowledge' => true]); ?>

<?= $form->field($model, 'baseUrl')->textInput(['disabled' => Yii::$app->settings->isFixed('baseUrl')]) ?>

<?= $form->field($model, 'paginationSize') ?>

<div class="container gx-0 overflow-x-hidden">
    <div class="row">
        <div class="col-lg-6">
            <?= $form->field($model, 'displayNameFormat')->dropDownList([
                '{username}' => Yii::t('AdminModule.settings', 'Username (e.g. john)'),
                '{profile.firstname} {profile.lastname}' => Yii::t('AdminModule.settings', 'Firstname Lastname (e.g. John Doe)'),
            ]) ?>
        </div>
        <div class="col-lg-6">
            <?= $form->field($model, 'displayNameSubFormat')->dropDownList($model->getDisplayNameSubAttributes()) ?>
        </div>
    </div>
</div>

<?= $form->field($model, 'spaceOrder')->dropDownList([
    '0' => Yii::t('AdminModule.settings', 'Custom sort order (alphabetical if not defined)'),
    '1' => Yii::t('AdminModule.settings', 'Last visit'),
]) ?>

<?= $form->field($model, 'defaultStreamSort')->dropDownList($model->getDefaultStreamSortOptions()) ?>

<?= $form->field($model, 'postTitleMode')->dropDownList($model->getPostTitleModeOptions()) ?>

<?= $form->beginCollapsibleFields(Yii::t('AdminModule.settings', 'Dashboard')) ?>
<?= $form->field($model, 'tour')->checkbox() ?>
<?= $form->field($model, 'dashboardShowProfilePostForm')->checkbox() ?>
<?= $form->field($model, 'dashboardShowTopicSidebar')->checkbox() ?>
<?= $form->endCollapsibleFields() ?>

<?= $form->beginCollapsibleFields(Yii::t('AdminModule.settings', 'Friendship')) ?>
<?= $form->field($model, 'enableFriendshipModule')->checkbox() ?>
<?= $form->endCollapsibleFields() ?>

<?= $form->beginCollapsibleFields(Yii::t('AdminModule.settings', 'Maintenance mode'), !$model->maintenanceMode) ?>
<?= Alert::warning(Yii::t('AdminModule.settings', 'Maintenance mode restricts access to the platform and immediately logs out all users except Admins.'))
    ->closeButton(false)
    ->icon(Icon::get('exclamation-triangle')) ?>
<?= $form->field($model, 'maintenanceMode')->checkbox() ?>
<?= $form->field($model, 'maintenanceModeInfo')->textInput(['placeholder' => Yii::t('AdminModule.settings', 'Add individual info text...')]) ?>
<?= $form->endCollapsibleFields() ?>

<hr>

<?= Html::submitButton(Yii::t('AdminModule.settings', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => ""]) ?>

<?php ActiveForm::end(); ?>
<?php ContentCard::end() ?>
