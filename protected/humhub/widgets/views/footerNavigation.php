<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\widgets\PoweredBy;
use yii\helpers\Html;

?>

<?php if ($numItems > 0): ?>
    <div class="text-center footer-nav">
        <small>
            <?php foreach ($items as $item): ?>
                <?= Html::a($item['label'], $item['url']); ?>&nbsp;&middot;&nbsp;
            <?php endforeach; ?>
            <?= PoweredBy::widget(); ?>
        </small>
    </div>
    <br/>
<?php endif; ?>
