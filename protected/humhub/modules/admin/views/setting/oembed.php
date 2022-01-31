<?php

use humhub\modules\admin\models\forms\OEmbedSettingsForm;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\widgets\Button;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var array $providers */
/* @var OEmbedSettingsForm $settings */

$this->registerJs(<<<JS
    $('[data-toggle="tooltip"]').tooltip();
JS, View::POS_READY);

?>

<?php $this->beginContent('@admin/views/setting/_advancedLayout.php') ?>

<p class="pull-right">
    <?= Html::a(Yii::t('AdminModule.settings', 'Add new provider'), Url::to(['oembed-edit']), ['class' => 'btn btn-success']); ?>
</p>

<h4><?= Yii::t('AdminModule.settings', 'Enabled OEmbed providers'); ?></h4>

<?php if (count($providers) != 0): ?>
    <div id="oembed-providers">
        <?php foreach ($providers as $providerName => $provider) : ?>
            <div class="oembed-provider-container col-xs-6 col-md-3">
                <div class="oembed-provider">

                    <div class="oembed-provider-name">
                        <span>
                            <?= Html::encode($providerName) ?>
                        </span>
                        <?php parse_str($provider['endpoint'], $query); ?>
                        <?php if (isset($query['access_token']) && empty($query['access_token'])): ?>
                            <span class="label label-danger label-error"
                                  data-toggle="tooltip" data-placement="right"
                                  title="<?= Yii::t('AdminModule.settings', 'Access token is not provided yet.') ?>">
                                <i class="fa fa-exclamation-circle" aria-hidden="true"></i>
                            </span>
                        <?php endif; ?>
                    </div>

                    <?= Html::a(Yii::t('base', 'Edit'), Url::to(['oembed-edit', 'name' => $providerName]), ['data-method' => 'POST', 'class' => 'btn btn-xs btn-link']); ?>

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
