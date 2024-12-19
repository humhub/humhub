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
<span class="wallentry-labels">
    <?php foreach ($object->getLabels() as $label) : ?>
        <?= $label ?>
    <?php endforeach; ?>
<span>
