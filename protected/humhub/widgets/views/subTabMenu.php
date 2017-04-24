<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/**
 * Tab Navigation by MenuWidget.
 *
 * @package humhub.widgets
 * @since 0.5 */

use \yii\helpers\Html;
?>
<ul id="tabs" class="nav nav-tabs tab-sub-menu">
    <?php foreach ($this->context->getItems() as $item) {?>
        <li <?= Html::renderTagAttributes($item['htmlOptions'])?>>
        <?= Html::a($item['label'], $item['url']); ?>
    </li>
    <?php }; ?>
</ul>
