<?php

use humhub\modules\admin\models\forms\OEmbedSettingsForm;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\widgets\Button;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var array $providers */
/* @var OEmbedSettingsForm $settings */
?>

<?php $this->beginContent('@admin/views/setting/_advancedLayout.php') ?>

<p class="pull-right">
    <?= Html::a(Yii::t('AdminModule.settings', 'Add new provider'), Url::to(['oembed-edit']), ['class' => 'btn btn-success']); ?>
</p>

<h4><?= Yii::t('AdminModule.settings', 'Enabled OEmbed providers'); ?></h4>

<?php if (count($providers) != 0): ?>
    <ul>
        <?php foreach ($providers as $providerUrl => $providerOEmbedAPI) : ?>
            <li><?= Html::a($providerUrl, Url::to(['oembed-edit', 'prefix' => $providerUrl]), ['data-method' => 'POST']); ?></li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p><strong><?= Yii::t('AdminModule.settings', 'Currently no provider active!'); ?></strong></p>
<?php endif; ?>

<?php $form = ActiveForm::begin() ?>

    <?= $form->field($settings, 'requestConfirmation')->checkbox() ?>

    <?= Button::primary(Yii::t('AdminModule.settings', 'Save'))->submit() ?>

<?php ActiveForm::end(); ?>

<?php $this->endContent(); ?>
