<?php

use humhub\components\View;
use humhub\modules\space\models\Space;
use humhub\modules\space\modules\manage\models\ChangeOwnerForm;
use humhub\modules\space\modules\manage\widgets\MemberMenu;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\ContentCard;
use humhub\widgets\form\ActiveForm;

/* @var $this View */
/* @var $model ChangeOwnerForm */
/* @var $space Space */
?>
<?php $this->beginContent('@space/modules/manage/views/layouts/section.php', [
    'title' => Yii::t('SpaceModule.manage', 'Members'),
    'tabs' => new MemberMenu(['space' => $space]),
]) ?>
<?php ContentCard::begin([
    'title' => Yii::t('SpaceModule.manage', 'Owner'),
    'description' => Yii::t('SpaceModule.manage', 'As owner of this space you can transfer this role to another administrator in space.'),
]) ?>

    <?php $form = ActiveForm::begin([]); ?>

    <?= $form->field($model, 'ownerId')->dropDownList($model->getNewOwnerArray()) ?>

    <?= Button::danger(Yii::t('SpaceModule.manage', 'Transfer ownership'))->action('client.submit')->confirm() ?>

    <?php ActiveForm::end(); ?>

<?php ContentCard::end() ?>
<?php $this->endContent() ?>
