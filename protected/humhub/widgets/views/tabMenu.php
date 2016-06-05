<?php
/**
 * Tab Navigation by MenuWidget.
 *
 * @package humhub.widgets
 * @since 0.5 */

use \yii\helpers\Html;
?>
<div class="tab-menu">
      
<ul class="nav nav-tabs">
    <?php foreach ($this->context->getItems() as $item) {?>
        <li <?php echo Html::renderTagAttributes($item['htmlOptions'])?>>
        <?php echo Html::a($item['label'], $item['url']); ?>
    </li>
    <?php }; ?>
</ul>
</div>