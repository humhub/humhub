<?php

use humhub\libs\Html;
use humhub\modules\content\helpers\ContentContainerHelper;
use humhub\modules\content\models\Content;
use humhub\modules\activity\models\Activity;

/**
 * WallEntry used in a stream and the activity stream.
 *
 * @var Mixed $object a content object like Post
 * @var string $jsWidget js widget component
 * @var Content $entry the wall entry to display
 * @var String $content the output of the content object (wallOut)
 *
 * @deprecated since 1.7 This view is only used for deprecated WallEntry widgets
 */

$container = ContentContainerHelper::getCurrent();
$isPinned = $container && $entry->pinned && $container->contentcontainer_id === $entry->contentcontainer_id;
$isActivity = $entry->object_model === Activity::class;
?>

<?php if (!$isActivity) : ?>
    <?= Html::beginTag('div', [
        'class' => ($isPinned) ? 'wall-entry pinned-entry' : 'wall-entry',
        'data' => [
            'content-container-id' => $entry->contentcontainer_id,
            'stream-entry' => 1,
            'stream-pinned' => (int) $isPinned,
            'action-component' => $jsWidget,
            'content-key' => $entry->id
        ]
    ])?>
<?php endif; ?>

<?= $content ?>

<?php if (!$isActivity) : ?>
    <?= Html::endTag('div')?>
<?php endif; ?>

