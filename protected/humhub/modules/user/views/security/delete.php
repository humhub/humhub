<?php

use humhub\modules\user\models\forms\AccountDelete;
use humhub\widgets\bootstrap\Alert;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;

/* @var AccountDelete $model */
?>
<?php $this->beginContent('@user/views/security/_layout.php') ?>

<strong><?= Yii::t('UserModule.account', 'Are you sure that you want to delete your account?') ?></strong>
<br>
<br>
<?= Alert::danger(Yii::t('UserModule.account', 'All your personal data will be irrevocably deleted.'))->closeButton(false) ?>

<?php $form = ActiveForm::begin(['sudo' => true]) ?>

<?= Button::danger(Yii::t('UserModule.account', 'Delete account'))
    ->confirm(
        Yii::t('UserModule.account', 'Are you sure that you want to delete your account?'),
        Yii::t('UserModule.account', 'All your personal data will be irrevocably deleted.'),
    )
    ->submit() ?>

<?php ActiveForm::end() ?>

<?php $this->endContent() ?>
