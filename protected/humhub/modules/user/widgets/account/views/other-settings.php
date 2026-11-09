<?php

use humhub\modules\user\models\forms\AccountSettings;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;

/* @var AccountSettings $model */
?>
<?php $form = ActiveForm::begin(['id' => 'account-settings-form', 'action' => ['/user/account/edit-settings'], 'acknowledge' => true]) ?>

<?= $form->field($model, 'show_introduction_tour')->checkbox() ?>

<?= Button::save()->submit() ?>

<?php ActiveForm::end() ?>
