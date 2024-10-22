<?php

use humhub\modules\content\widgets\richtext\RichText;
use humhub\modules\content\widgets\stream\WallStreamEntryOptions;
use humhub\modules\post\models\Post;

/* @var $post Post */
/* @var $renderOptions WallStreamEntryOptions */
/* @var $enableDynamicFontSize bool */
/* @var $collapsedPostHeight int */

$isDetailView = $renderOptions->isViewContext(WallStreamEntryOptions::VIEW_CONTEXT_DETAIL);

?>
<div data-ui-widget="post.Post" <?php if (!$isDetailView): ?>data-state="collapsed"<?php endif; ?>
     data-dynamic-font-size="<?= intval($enableDynamicFontSize) ?>" data-ui-init id="post-content-<?= $post->id ?>">
    <div
        data-ui-markdown
        <?php if (!$isDetailView): ?>
            data-ui-show-more
        <?php endif; ?>
        <?php if ($collapsedPostHeight > 0): ?>
            data-collapse-at="<?= $collapsedPostHeight ?>"
        <?php endif; ?>
    >
        <?= RichText::output($post->message, ['record' => $post]) ?>
    </div>
</div>
