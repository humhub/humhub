<?php

use humhub\helpers\Html;
use humhub\modules\admin\models\forms\OEmbedSettingsForm;
use humhub\widgets\bootstrap\Badge;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;
use yii\helpers\Url;
use yii\web\View;

/* @var array $providers */
/* @var OEmbedSettingsForm $settings */

$this->registerJs(<<<JS
    $('[data-bs-toggle="tooltip"]').tooltip();
JS, View::POS_READY);

?>

<?php $this->beginContent('@admin/views/setting/_advancedLayout.php') ?>

<p class="float-end">
    <?= Html::a(Yii::t('AdminModule.settings', 'Add new provider'), Url::to(['oembed-edit']), ['class' => 'btn btn-success']); ?>
</p>

<h4><?= Yii::t('AdminModule.settings', 'Enabled OEmbed providers'); ?></h4>

<?php if (count($providers) != 0): ?>
    <div id="oembed-providers">
        <?php foreach ($providers as $providerName => $provider) : ?>
            <div class="oembed-provider-container col-6 col-lg-3">
                <div class="oembed-provider">

                    <div class="oembed-provider-name">
                        <span>
                            <?= Html::encode($providerName) ?>
                        </span>
                        <?php parse_str((string) $provider['endpoint'], $query); ?>
                        <?php if (isset($query['access_token']) && empty($query['access_token'])): ?>
                            <?= Badge::danger()
                                ->icon('fa-exclamation-circle')
                                ->right()
                                ->tooltip(Yii::t('AdminModule.settings', 'Access token is not provided yet.')) ?>
                        <?php endif; ?>
                    </div>

                    <?= Html::a(Yii::t('base', 'Edit'), Url::to(['oembed-edit', 'name' => $providerName]), ['data-method' => 'POST', 'class' => 'btn btn-sm btn-link']); ?>

                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p><strong><?= Yii::t('AdminModule.settings', 'Currently no provider active!'); ?></strong></p>
<?php endif; ?>

<hr>

<?php $form = ActiveForm::begin() ?>

<?= $form->field($settings, 'requestConfirmation')->checkbox() ?>

<?= Button::primary(Yii::t('AdminModule.settings', 'Save'))->submit() ?>

<?php ActiveForm::end(); ?>

<?php $this->endContent(); ?>
