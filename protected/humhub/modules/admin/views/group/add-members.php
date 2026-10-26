<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\View;
use humhub\modules\admin\models\forms\AddGroupMemberForm;
use humhub\modules\user\widgets\UserPickerField;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;
use yii\helpers\Url;

/* @var $this View */
/* @var $model AddGroupMemberForm */
?>

<?php $form = Modal::beginFormDialog([
    'title' => Yii::t('AdminModule.user', 'Add members'),
    'footer' => ModalButton::cancel() . ' ' . ModalButton::save(Yii::t('AdminModule.user', 'Add'))->submit(),
]) ?>

    <?= $form->field($model, 'userGuids')->widget(UserPickerField::class, [
        'url' => Url::to(['/admin/group/new-member-search', 'id' => $model->groupId]),
        'placeholder' => Yii::t('AdminModule.user', 'Add new members...'),
        'focus' => true,
    ])->label(false) ?>
    <?= $form->field($model, 'groupId')->hiddenInput()->label(false) ?>

<?php Modal::endFormDialog(); ?>
