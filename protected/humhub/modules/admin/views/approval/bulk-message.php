<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\admin\models\forms\ApproveUserBulkMessageForm;
use humhub\modules\content\widgets\richtext\RichTextField;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;

/* @var $this View */
/* @var $model ApproveUserBulkMessageForm */
?>

<?php $form = Modal::beginFormDialog([
    'title' => Yii::t('AdminModule.user', '{count, plural, =1{Send a message to 1 user} other{Send a message to # users}}', ['count' => count($model->ids)]),
    'footer' => ModalButton::cancel() . ' ' . ModalButton::save(Yii::t('AdminModule.user', 'Send'))->submit(),
]) ?>
    <?= $form->field($model, 'subject') ?>
    <?= $form->field($model, 'message')->widget(RichTextField::class, ['exclude' => ['oembed', 'upload']])
        ->hint(Yii::t('AdminModule.user', '{displayName} and {AdminName} are replaced for each user.', [
            '{displayName}' => '{displayName}',
            '{AdminName}' => '{AdminName}',
        ])) ?>
    <?php foreach ($model->ids as $id) : ?>
        <?= Html::hiddenInput(Html::getInputName($model, 'ids') . '[]', $id) ?>
    <?php endforeach; ?>
<?php Modal::endFormDialog(); ?>
