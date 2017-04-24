<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */
?>

<!-- check if flash message exists -->
<?php if(Yii::$app->getSession()->hasFlash('data-saved')) : ?>

    <script>
        $(function() {
            humhub.modules.log.success('<?= Yii::$app->getSession()->getFlash('data-saved'); ?>', true);
        });
    </script>

<?php endif; ?>
