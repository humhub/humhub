<span data-ui-widget="post.Post" data-state="collapsed" data-ui-init id="post-content-<?= $post->id; ?>" style="overflow: hidden; margin-bottom: 5px;">
    <?= humhub\widgets\RichText::widget(['text' => $post->message, 'record' => $post]) ?>
</span>
