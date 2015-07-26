<?php foreach ($this->context->getItemGroups() as $group) : ?>

    <?php $items = $this->context->getItems($group['id']); ?>
    <?php if (count($items) == 0) continue; ?>

    <ul class="nav nav-pills pull-left">
        <?php foreach ($items as $item) : ?>
            <?php //$item['htmlOptions']['class'] .= " list-group-item"; ?>


            <li role="presentation">  <?php echo \yii\helpers\Html::a($item['icon'] . "<span> " . $item['label'] . "</span>", $item['url'], $item['htmlOptions']); ?></li>
        <?php endforeach; ?>
    </ul>
<?php endforeach; ?>
