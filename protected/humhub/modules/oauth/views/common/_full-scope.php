<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\oauth\components\ScopeRegistry;
use yii\helpers\Json;

/**
 * Checking `full` in a scope checkbox list checks and disables the other scopes; unchecking it
 * restores their previous state. Disabled checkboxes are not submitted, the form model stores
 * `full` alone.
 *
 * @var View $this
 * @var string $listId the id of the checkbox list
 */
?>
<script <?= Html::nonce() ?>>
    $(function () {
        const $list = $(<?= Json::htmlEncode('#' . $listId) ?>);
        const $full = $list.find('input[type=checkbox]').filter(function () {
            return this.value === <?= Json::htmlEncode(ScopeRegistry::FULL) ?>;
        });
        const $others = $list.find('input[type=checkbox]').not($full);

        const apply = function () {
            if ($full.is(':checked')) {
                $others.filter(':enabled').each(function () {
                    $(this).data('oauthChecked', this.checked);
                });
                $others.prop({checked: true, disabled: true});
            } else {
                $others.filter(':disabled').each(function () {
                    $(this).prop({disabled: false, checked: $(this).data('oauthChecked') === true});
                });
            }
        };

        $full.on('change', apply);
        apply();
    });
</script>
