<?php

use humhub\modules\admin\models\forms\OEmbedProviderForm;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\widgets\Button;
use yii\helpers\Url;
use yii\web\View;

/* @var $name string */
/** @var OEmbedProviderForm $model */

parse_str($model->endpoint, $query);

$this->registerJs(<<<JS
    function initEndpointInputs() {
        var url = new URL($('#oembedproviderform-endpoint').val());
        var formGroup;

        $('#endpoint-parameters').html('');

        for (var key of url.searchParams.keys()) {
            if (key !== 'url' && key !== 'access_token') {
                var value = url.searchParams.get(key);
                var label = key[0].toUpperCase() + key.substring(1)
                    .replace(/_([a-z])/, function (m, w) {
                        return w.toUpperCase();
                    }).replace(/-([a-z])/, function (m, w) {
                        return w.toUpperCase();
                    }).replace(/([A-Z])/, " $1");
                var inputId = 'oembedproviderform-' + key;

                formGroup = '<div class="form-group col-xs-12 col-sm-6">' +
                    '<label for="' + inputId + '" class="control-label" type="text">' + label + '</label>' +
                    '<input id="' + inputId + '" value="' + (!value.match(/\%\w+\%/) ? value : "") + '" type="text" class="form-control endpoint-param" data-param-name="' + key + '">' +
                    '</div>';

                $('#endpoint-parameters').append(formGroup);
            }
        }

        $('input[data-param-name]').on('input change', composeEndpoint);
    }

    function composeEndpoint() {
        var endpointInput = $('#oembedproviderform-endpoint');
        var url = new URL(endpointInput.val());

        $('.endpoint-param').each(function (index) {
            url.searchParams.set($(this).attr('data-param-name'), $(this).val());
        });

        endpointInput.val(decodeURI(url.toString()));
    }

    $('#oembedproviderform-endpoint').on('input change', function () {
        initEndpointInputs();
    });

    $('#oembed-provider-form').on('submit', function () {
        composeEndpoint();
    });

JS, View::POS_END);

$this->registerJs(<<<JS
    initEndpointInputs();
JS, View::POS_LOAD);

?>

<?php $this->beginContent('@admin/views/setting/_advancedLayout.php') ?>

<div class="clearfix">
    <?= Button::back(Url::to(['setting/oembed']), Yii::t('AdminModule.settings', 'Back to overview')) ?>
    <h4 class="pull-left">
        <?php
        if (empty($name)) {
            echo Yii::t('AdminModule.settings', 'Add OEmbed provider');
        } else {
            echo Yii::t('AdminModule.settings', 'Edit OEmbed provider');
        }
        ?>
    </h4>
</div>

<br>

<?php $form = ActiveForm::begin(['id' => 'oembed-provider-form', 'acknowledge' => true]); ?>

<?= $form->errorSummary($model); ?>

<?= $form->field($model, 'name')->textInput(['class' => 'form-control']); ?>

<?= $form->field($model, 'pattern')->textInput(['class' => 'form-control']); ?>
<p class="help-block"><?= Yii::t('AdminModule.settings', 'Regular expression by which the link match will be checked.'); ?></p>

<?= $form->field($model, 'endpoint')->textInput(['class' => 'form-control']); ?>
<p class="help-block"><?= Yii::t('AdminModule.settings', 'Use %url% as placeholder for URL. Format needs to be JSON. (e.g. http://www.youtube.com/oembed?url=%url%&format=json)'); ?></p>

<?php if(isset($query['access_token'])): ?>
    <?= $form->field($model, 'access_token')->textInput(['class' => 'form-control endpoint-param', 'data-param-name' => 'access_token', 'value' => $query['access_token']]) ?>
<?php endif; ?>

<div id="endpoint-parameters"></div>

<?= Button::primary(Yii::t('AdminModule.settings', 'Save'))->submit(); ?>

<?php if (!empty($name)): ?>
    <?= Button::danger(Yii::t('AdminModule.settings', 'Delete'))
        ->link(['oembed-delete', 'name' => $name])
        ->confirm()
        ->right()
        ->options(['data-method' => 'POST']) ?>
<?php endif; ?>

<?php ActiveForm::end(); ?>

<?php $this->endContent();