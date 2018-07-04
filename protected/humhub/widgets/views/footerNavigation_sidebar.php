<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use yii\helpers\Html;

$i = 0;
?>

<?php if ($numItems > 0): ?>
    <div class="footer-nav">
        <small>

            <?php foreach ($items as $item): ?>
                <?= Html::a($item['label'], $item['url']); ?>

                <?php if (++$i !== $numItems): ?>
                    &nbsp;&middot;&nbsp;
                <?php endif; ?>

            <?php endforeach; ?>
            &middot; Powered&nbsp;by&nbsp;<?= Html::a('HumHub', 'https://humhub.org'); ?>&nbsp;
        </small>
    </div>
    <br />
<?php endif; ?>
