<div class="profile-interests">
    <h3>Interests</h3>
    <?php if ($user->tags) : ?>
    <div>
        <!-- start: tags for user skills -->
        <div class="tags">
            <?php foreach ($user->getTags() as $tag) { ?>
            <p>
                <?php echo HHtml::link($tag, $this->createUrl('//directory/directory/members', array('keyword' => 'tags:' . $tag, 'areas' => array('User')))); ?>
            </p>
            <?php } ?>
        </div>
        <!-- end: tags for user skills -->
    </div>
    <?php else : ?>
    <p><?= CHtml::encode($user->displayName); ?> has not defined any interests yet.</p>

    <?php endif; ?>
</div>