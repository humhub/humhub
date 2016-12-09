<div class="modal-dialog modal-dialog-extra-small animated pulse" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-content">
        <?php if (!(empty($success))) : ?>
            <script>
                $(function () {humhub.modules.ui.status.success('<?= $success ?>')});
            </script>
        <?php elseif ($saved) : ?>
            <script>
                $(function () {humhub.modules.ui.status.success('<?= Yii::t('base', 'Saved') ?>')});
            </script>
        <?php elseif (!(empty($error))) : ?>
            <script>
                $(function () {humhub.modules.ui.status.error('<?= $error ?>')});
            </script>
        <?php elseif (!(empty($warn))) : ?>
            <script>
                $(function () {humhub.modules.ui.status.warn('<?= $warn ?>')});
            </script>
        <?php elseif (!(empty($info))) : ?>
            <script>
                $(function () {humhub.modules.ui.status.info('<?= $info ?>')});
            </script>
        <?php endif; ?>
        <script>
            $(function () {
                humhub.modules.ui.modal.global.close();
            });
        </script>
    </div>
</div>
