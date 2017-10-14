<?php
/**
 * Left Navigation by MenuWidget.
 *
 * @package humhub.widgets
 * @since 0.5
 */
?>

<!-- start: list-group navi for large devices -->
<div id="<?= $this->context->id; ?>" class="panel panel-default left-navigation">
    <?php foreach ($this->context->getItemGroups() as $group) : ?>

        <?php $items = $this->context->getItems($group['id']); ?>
        <?php if (count($items) == 0) continue; ?>

        <?php if ($group['label'] != "") : ?>
            <div class="panel-heading"><?php echo $group['label']; ?></div>
        <?php endif; ?>
        <div class="list-group">
            <?php foreach ($items as $item) : ?>
                <?php $item['htmlOptions']['class'] .= " list-group-item"; ?>
                <?php echo \yii\helpers\Html::a($item['icon'] . "<span>" . $item['label'] . "</span>", $item['url'], $item['htmlOptions']); ?>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

</div>
<!-- end: list-group navi for large devices -->
