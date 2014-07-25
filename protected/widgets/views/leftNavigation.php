<?php
/**
 * Left Navigation by MenuWidget.
 *
 * @package humhub.widgets
 * @since 0.5
 */
?>

<!-- start: list-group navi for large devices -->
<div class="panel panel-default">
    <?php foreach ($this->getItemGroups() as $group) : ?>

        <?php $items = $this->getItems($group['id']); ?>
        <?php if (count($items) == 0) continue; ?>

        <?php if ($group['label'] != "") : ?>
            <div class="panel-heading"><?php echo $group['label']; ?></div>
        <?php endif; ?>
        <div class="list-group">
            <?php foreach ($items as $item) : ?>
                <a href="<?php echo $item['url']; ?>"
                   class="list-group-item <?php if ($item['isActive']): ?>active<?php endif; ?><?php if (isset($item['id'])) {echo $item['id'];} ?>">
                    <?php echo $item['icon']; ?>
                    <span><?php echo $item['label']; ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

</div>
<!-- end: list-group navi for large devices -->

