<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\libs\Html;
use humhub\modules\file\models\FileContent;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\widgets\ModalButton;
use humhub\widgets\ModalDialog;

/* @var $file FileContent */
?>

<?php ModalDialog::begin([
        'header' => Yii::t('FileModule.base', '<strong>Edit</strong> file', ['fileName' => Html::encode($file->file_name)]),
        'options' => ['style' => 'width:95%'],
    ]) ?>
    <?php $form = ActiveForm::begin() ?>
        <div class="modal-body">
            <h3 style="padding-top:0px;margin-top:0px"><?= Html::encode($file->file_name) ?></h3>
            <br />

            <?= $form->field($file, 'newFileContent')->textarea(['rows' => 10])->label(false) ?>

            <div class="clearfix"></div>
        </div>

        <div class="modal-footer">
            <hr />
            <?= ModalButton::save(Yii::t('FileModule.base', 'Save'))->submit()->action('updateFileContent')->left() ?>
            <?= ModalButton::cancel(Yii::t('FileModule.base', 'Close'))->right() ?>
        </div>
    <?php ActiveForm::end() ?>

<?php ModalDialog::end(); ?>