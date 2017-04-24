<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\libs\Html;
?>
<div class="modal-dialog modal-dialog-extra-small animated pulse" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-content">
        <script>
        <?php if (!(empty($success))) : ?>
            $(function () {humhub.modules.ui.status.success('<?= Html::encode($success) ?>')});
        <?php elseif ($saved) : ?>
            $(function () {humhub.modules.ui.status.success('<?= Html::encode(Yii::t('base', 'Saved')) ?>')});
        <?php elseif (!(empty($error))) : ?>
            $(function () {humhub.modules.ui.status.error('<?= Html::encode($error) ?>')});
        <?php elseif (!(empty($warn))) : ?>
            $(function () {humhub.modules.ui.status.warn('<?= Html::encode($warn) ?>')});
        <?php elseif (!(empty($info))) : ?>
            $(function () {humhub.modules.ui.status.info('<?= Html::encode($info) ?>')});
        <?php endif; ?>
            $(function () {
                humhub.modules.ui.modal.global.close();
            });
        </script>
    </div>
</div>
