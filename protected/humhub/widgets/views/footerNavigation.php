<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\widgets\PoweredBy;
use yii\helpers\Html;

foreach ($items as $item) {
	$itemsHtml[] = Html::a($item['label'], $item['url']);
}
?>

<?php if ($numItems > 0): ?>
    <div class="text-center footer-nav">
        <small>
        	<?= implode('&nbsp;&middot;&nbsp', $itemsHtml) ?>
            <?= PoweredBy::widget(); ?>
        </small>
    </div>
    <br/>
<?php endif; ?>
