<?php
 use humhub\libs\Html;

 /* @var $success string */
 /* @var $saved boolean */
 /* @var $error string */
 /* @var $warn string */
 /* @var $info string */
 /* @var $script string */
 /* @var $reload boolean*/

?>
<div class="modal-dialog modal-dialog-extra-small animated pulse" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-content">
        <?php if (!(empty($success))) : ?>
            <script <?= \humhub\libs\Html::nonce() ?>>
                $(function () {humhub.modules.ui.status.success('<?= Html::encode($success) ?>')});
            </script>
        <?php elseif ($saved) : ?>
            <script <?= \humhub\libs\Html::nonce() ?>>
                $(function () {humhub.modules.ui.status.success('<?= Html::encode(Yii::t('base', 'Saved')) ?>')});
            </script>
        <?php elseif (!(empty($error))) : ?>
            <script <?= \humhub\libs\Html::nonce() ?>>
                $(function () {humhub.modules.ui.status.error('<?= Html::encode($error) ?>')});
            </script>
        <?php elseif (!(empty($warn))) : ?>
            <script <?= \humhub\libs\Html::nonce() ?>>
                $(function () {humhub.modules.ui.status.warn('<?= Html::encode($warn) ?>')});
            </script>
        <?php elseif (!(empty($info))) : ?>
            <script <?= \humhub\libs\Html::nonce() ?>>
                $(function () {humhub.modules.ui.status.info('<?= Html::encode($info) ?>')});
            </script>
        <?php endif; ?>
        <script <?= \humhub\libs\Html::nonce() ?>>
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
