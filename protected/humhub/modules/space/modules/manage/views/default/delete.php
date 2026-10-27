<?php

use humhub\components\View;
use humhub\modules\space\models\Space;
use humhub\modules\space\modules\manage\models\DeleteForm;
use humhub\modules\space\modules\manage\widgets\DefaultMenu;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\ContentCard;
use humhub\widgets\form\ActiveForm;

/* @var $this View
 * @var $model DeleteForm
 * @var $space Space
 */

?>
<?php $this->beginContent('@space/modules/manage/views/layouts/section.php', [
    'title' => Yii::t('SpaceModule.manage', 'Settings'),
    'tabs' => new DefaultMenu(['space' => $space]),
]) ?>
<?php ContentCard::begin([
    'title' => Yii::t('SpaceModule.manage', 'Delete space'),
    'backUrl' => $space->createUrl('advanced'),
    'backLabel' => Yii::t('SpaceModule.manage', 'Back to advanced settings'),
]) ?>
    <p><?= Yii::t('SpaceModule.manage', 'Are you sure, that you want to delete this space? All published content will be removed!'); ?></p>
    <p><?= Yii::t('SpaceModule.manage', 'Please type the name of the space to proceed.'); ?></p>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'confirmSpaceName'); ?>

    <?= Button::danger(Yii::t('SpaceModule.manage', 'Delete'))->confirm()->submit() ?>

    <?php ActiveForm::end(); ?>
<?php ContentCard::end() ?>
<?php $this->endContent() ?>
