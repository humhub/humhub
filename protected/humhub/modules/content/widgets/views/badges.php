<?php

use humhub\modules\content\components\ContentActiveRecord;

/**
 * This view shows common labels for wall entries.
 * Its used by WallEntryLabelWidget.
 *
 * @var ContentActiveRecord $object the content object (e.g. Post)
 *
 * @since 0.5
 */
?>
<span class="wallentry-badges">
    <?php foreach ($object->getBadges() as $badge) : ?>
        <?= $badge ?>
    <?php endforeach; ?>
<span>
