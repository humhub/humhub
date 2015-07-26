<?php foreach ($this->context->getItemGroups() as $group) : ?>

    <?php $items = $this->context->getItems($group['id']); ?>
    <?php if (count($items) == 0) continue; ?>

    <ul class="nav nav-pills pull-right">
        <li class="dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#" aria-expanded="false"><i
                    class="fa fa-cogs"></i></a>

            <?php //$item['htmlOptions']['class'] .= " list-group-item"; ?>

            <ul class="dropdown-menu pull-right">
                <?php foreach ($items as $item) : ?>
                <li role="presentation">  <?php echo \yii\helpers\Html::a($item['icon'] . "<span> " . $item['label'] . "</span>", $item['url'], $item['htmlOptions']); ?></li>
                <?php endforeach; ?>
            </ul>
        </li>
    </ul>
<?php endforeach; ?>
