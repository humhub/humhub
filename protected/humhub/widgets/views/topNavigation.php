<?php

use yii\helpers\Html;
use yii\helpers\Url;
?>
<?php foreach ($this->context->getItems() as $item) : ?>
    <li class="visible-md visible-lg <?php if ($item['isActive']): ?>active<?php endif; ?> <?php
    if (isset($item['id'])) {
        echo $item['id'];
    }
    ?>">
            <?php echo Html::a($item['icon'] . "<br />" . $item['label'], $item['url'], $item['htmlOptions']); ?>
    </li>
<?php endforeach; ?>

<li class="dropdown visible-xs visible-sm">
    <a href="#" id="top-dropdown-menu" class="dropdown-toggle" data-toggle="dropdown">
        <i class="fa fa-align-justify"></i><br>
        <?php echo Yii::t('base', 'Menu'); ?>
        <b class="caret"></b></a>
    <ul class="dropdown-menu">

        <?php foreach ($this->context->getItems() as $item) : ?>
            <li class="<?php if ($item['isActive']): ?>active<?php endif; ?>">
                <?php echo Html::a($item['label'], $item['url'], $item['htmlOptions']); ?>
            </li>
        <?php endforeach; ?>

    </ul>
</li>