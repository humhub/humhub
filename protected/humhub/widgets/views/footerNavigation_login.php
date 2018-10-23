<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use yii\helpers\Html;
use humhub\widgets\PoweredBy;

$i = 0;
?>

<div class="text text-center powered">
    <?php if ($numItems > 0): ?>
        <?php foreach ($items as $item): ?>
            <?= Html::a($item['label'], $item['url']); ?>

            <?php if (++$i !== $numItems): ?>
                &nbsp;&middot;&nbsp;
            <?php endif; ?>

        <?php endforeach; ?>
        <br/><br/>
    <?php endif; ?>
    <?= PoweredBy::widget(); ?>
</div>
