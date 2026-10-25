<?php

use humhub\compat\HForm;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\ContentCard;
use humhub\widgets\form\ActiveForm;

/* @var $hForm HForm */
/* @var $user \humhub\modules\user\models\User */
?>

<?php ContentCard::begin([
    'title' => Yii::t('AdminModule.user', 'Edit user: {name}', ['name' => $user->displayName]),
    'buttons' => Button::back(['index'], Yii::t('AdminModule.base', 'Back to overview'))->right(false)->sm()->asString(),
]) ?>
<?php $form = ActiveForm::begin(['options' => ['data-ui-widget' => 'ui.form.TabbedForm', 'data-ui-init' => '', 'style' => 'display:none']]); ?>
<?= $hForm->render($form); ?>
<?php ActiveForm::end(); ?>
<?php ContentCard::end() ?>
