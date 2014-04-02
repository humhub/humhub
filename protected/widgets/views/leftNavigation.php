<?php
/**
 * Left Navigation by MenuWidget.
 *
 * @package humhub.widgets
 * @since 0.5
 */
?>

<!-- start: list-group navi for large devices -->
<div class="list-group">
    <?php foreach ($this->getItemGroups() as $group) : ?>

        <?php $items = $this->getItems($group['id']); ?>
        <?php if (count($items) == 0) continue; ?>

        <?php if ($group['label'] != "") : ?>
            <span class="list-group-item"><h4><?php echo $group['label']; ?></h4></span>
        <?php endif; ?>

        <?php foreach ($items as $item) : ?>
            <a href="<?php echo $item['url']; ?>" class="list-group-item <?php if ($item['isActive']): ?>active<?php endif; ?>">
                <?php echo $item['icon']; ?>
                <span><?php echo $item['label']; ?></span>
            </a>
        <?php endforeach; ?>
    <?php endforeach; ?>
</div>
<!-- end: list-group navi for large devices -->
