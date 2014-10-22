<?php
/**
 * Tab Navigation by MenuWidget.
 *
 * @package humhub.widgets
 * @since 0.5 */
?>
<ul class="nav nav-tabs">
    <?php foreach ($this->getItems() as $item) : ?>
        <li class="<?php if ($item['isActive']): ?>active<?php endif; ?>">
            <a href="<?php echo $item['url']; ?>" target="<?php echo $item['target']; ?>">
                <?php //echo $item['icon']; ?>
                <?php echo $item['label']; ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>
