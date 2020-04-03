<?php

use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\modules\space\modules\manage\widgets\DefaultMenu;
use humhub\widgets\Button;

/* @var $this \humhub\components\View
 * @var $model \humhub\modules\space\modules\manage\models\DeleteForm
 */

?>

<div class="panel panel-default">
    <div class="panel-heading">
        <?= Yii::t('SpaceModule.manage', '<strong>Space</strong> settings'); ?>
    </div>

    <?= DefaultMenu::widget(['space' => $space]); ?>

    <div class="panel-body">
        <p><?= Yii::t('SpaceModule.manage', 'Are you sure, that you want to delete this space? All published content will be removed!'); ?></p>
        <p><?= Yii::t('SpaceModule.manage', 'Please type the name of the space to proceed.'); ?></p>
        <br>

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'confirmSpaceName'); ?>

        <hr>
        <?= Button::danger(Yii::t('SpaceModule.manage', 'Delete'))->confirm()->submit() ?>

        <?php ActiveForm::end(); ?>
    </div>
</div>
