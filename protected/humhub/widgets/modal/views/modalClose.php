<?php

use humhub\widgets\bootstrap\Html;

/* @var $success string */
/* @var $saved boolean */
/* @var $error string */
/* @var $warn string */
/* @var $info string */
/* @var $script string */
/* @var $reload boolean*/
?>

<div class="modal-dialog" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-content">
        <?php if (!(empty($success))) : ?>
            <script <?= Html::nonce() ?>>
                $(function () {humhub.modules.ui.status.success('<?= Html::encode($success) ?>')});
            </script>
        <?php elseif ($saved) : ?>
            <script <?= Html::nonce() ?>>
                $(function () {humhub.modules.ui.status.success('<?= Html::encode(Yii::t('base', 'Saved')) ?>')});
            </script>
        <?php elseif (!(empty($error))) : ?>
            <script <?= Html::nonce() ?>>
                $(function () {humhub.modules.ui.status.error('<?= Html::encode($error) ?>')});
            </script>
        <?php elseif (!(empty($warn))) : ?>
            <script <?= Html::nonce() ?>>
                $(function () {humhub.modules.ui.status.warn('<?= Html::encode($warn) ?>')});
            </script>
        <?php elseif (!(empty($info))) : ?>
            <script <?= Html::nonce() ?>>
                $(function () {humhub.modules.ui.status.info('<?= Html::encode($info) ?>')});
            </script>
        <?php endif; ?>
        <script <?= Html::nonce() ?>>
            $(function () {
                humhub.modules.ui.modal.global.close();
                <?php if($script) : ?>
                    <?= $script ?>
                <?php endif; ?>
                <?php if($reload) : ?>
                    humhub.modules.client.reload();
                <?php endif; ?>
            });
        </script>
    </div>
</div>
