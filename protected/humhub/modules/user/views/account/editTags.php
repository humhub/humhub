<?php

use humhub\modules\content\widgets\ContainerTagPicker;
use humhub\modules\user\models\User;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;

/* @var User $user */
?>

<?php $this->beginContent('@user/views/account/_userProfileLayout.php', [
    'description' => Yii::t('UserModule.account', 'Add tags to your profile describing you and highlighting your skills and interests. Your tags will be displayed in your profile and in the \'People\' directory.'),
]) ?>
<?php $form = ActiveForm::begin(['id' => 'profile-tags-form', 'acknowledge' => true]) ?>

<?= $form->field($user, 'tagsField')->widget(ContainerTagPicker::class, ['minInput' => 2]) ?>

<?= Button::save()->submit() ?>

<?php ActiveForm::end() ?>
<?php $this->endContent() ?>
