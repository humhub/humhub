<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

use humhub\components\View;
use humhub\modules\topic\models\forms\ContentTopicsForm;
use humhub\modules\topic\widgets\TopicPicker;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;

/* @var $this View */
/* @var $model ContentTopicsForm */

?>

<?php $form = Modal::beginFormDialog([
    'title' => Yii::t('TopicModule.base', '<strong>Manage</strong> Topics'),
    'footer' => ModalButton::cancel() . ' ' . ModalButton::save(),
]) ?>

<?= $form->field($model, 'topics')->widget(TopicPicker::class, ['contentContainer' => $model->getContentContainer(), 'options' => ['autofocus' => '']])->label(false) ?>

<?php Modal::endFormDialog() ?>
