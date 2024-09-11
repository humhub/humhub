<!-- check if flash message exists -->
<?php if(Yii::$app->getSession()->hasFlash('data-saved')): ?>

    <script <?= \humhub\widgets\bootstrap\Html::nonce() ?>>
        $(function() {
            humhub.modules.log.success('<?php echo Yii::$app->getSession()->getFlash('data-saved'); ?>', true);
        });
    </script>

<?php endif; ?>
