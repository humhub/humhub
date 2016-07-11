<?php
/**
 * Tab Navigation by MenuWidget.
 *
 * @package humhub.widgets
 * @since 0.5 */

use \yii\helpers\Html;
?>
<ul id="tabs" class="nav nav-tabs tab-sub-menu">
    <?php foreach ($this->context->getItems() as $item) {?>
        <li <?php echo Html::renderTagAttributes($item['htmlOptions'])?>>
        <?php echo Html::a($item['label'], $item['url']); ?>
    </li>
    <?php }; ?>
</ul>
