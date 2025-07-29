<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

use humhub\components\View;
use humhub\modules\topic\models\Topic;
use humhub\widgets\form\SortOrderField;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;

/* @var $this View */
/* @var $model Topic */
?>

<?php $form = Modal::beginFormDialog([
    'title' => Yii::t('TopicModule.base', '<strong>Edit</strong> Topic'),
    'footer' => ModalButton::cancel() . ' ' . ModalButton::save()->submit(),
]) ?>

    <?= $form->field($model, 'name') ?>
    <?= $form->field($model, 'sort_order')->widget(SortOrderField::class) ?>

<?php Modal::endFormDialog(); ?>
