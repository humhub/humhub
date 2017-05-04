<?php
 use humhub\libs\Html;
?>
<div class="modal-dialog modal-dialog-extra-small animated pulse" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-content">
        <?php if (!(empty($success))) : ?>
            <script>
                $(function () {humhub.modules.ui.status.success('<?= Html::encode($success) ?>')});
            </script>
        <?php elseif ($saved) : ?>
            <script>
                $(function () {humhub.modules.ui.status.success('<?= Html::encode(Yii::t('base', 'Saved')) ?>')});
            </script>
        <?php elseif (!(empty($error))) : ?>
            <script>
                $(function () {humhub.modules.ui.status.error('<?= Html::encode($error) ?>')});
            </script>
        <?php elseif (!(empty($warn))) : ?>
            <script>
                $(function () {humhub.modules.ui.status.warn('<?= Html::encode($warn) ?>')});
            </script>
        <?php elseif (!(empty($info))) : ?>
            <script>
                $(function () {humhub.modules.ui.status.info('<?= Html::encode($info) ?>')});
            </script>
        <?php endif; ?>
        <script>
            $(function () {
                humhub.modules.ui.modal.global.close();
            });
        </script>
    </div>
</div>
