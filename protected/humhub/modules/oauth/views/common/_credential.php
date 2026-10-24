<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\widgets\bootstrap\Button;

/**
 * A credential shown once, with copy buttons.
 *
 * @var View $this
 * @var string $hint
 * @var array<array{id: string, label: string, value: string, copy?: bool}> $fields
 * @var string $copied status message after copying
 * @var array|string $doneUrl
 */
?>
<p class="text-body-secondary"><?= Html::encode($hint) ?></p>

<?php foreach ($fields as $field): ?>
    <div class="mb-3">
        <label class="form-label" for="<?= Html::encode($field['id']) ?>"><?= Html::encode($field['label']) ?></label>
        <div class="input-group">
            <input type="text" id="<?= Html::encode($field['id']) ?>" class="form-control font-monospace oauth-credential" readonly
                   value="<?= Html::encode($field['value']) ?>">
            <?php if (!empty($field['copy'])): ?>
                <?= Button::light(Yii::t('OauthModule.base', 'Copy'))
                    ->icon('copy')
                    ->cssClass('oauth-credential-copy')
                    ->options(['data-target' => $field['id']])
                    ->loader(false) ?>
            <?php endif ?>
        </div>
    </div>
<?php endforeach ?>

<?= Button::primary(Yii::t('OauthModule.base', 'Done'))->link($doneUrl) ?>

<script <?= Html::nonce() ?>>
    $(function () {
        $('.oauth-credential').on('focus click', function () {
            this.select();
        });
        $('.oauth-credential-copy').on('click', function (event) {
            event.preventDefault();
            const $input = $('#' + $(this).data('target'));
            $input.trigger('select');
            navigator.clipboard.writeText($input.val()).then(function () {
                humhub.modules.ui.status.success(<?= json_encode($copied, JSON_HEX_TAG) ?>);
            });
        });
    });
</script>
