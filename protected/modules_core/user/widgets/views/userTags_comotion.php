<h3>Interests</h3>
<?php if ($user->tags) : ?>
    <div class="panel-body">
        <!-- start: tags for user skills -->
        <div class="tags">
            <?php foreach ($user->getTags() as $tag) { ?>
                <?php echo HHtml::link($tag, $this->createUrl('//directory/directory/members', array('keyword' => 'tags:' . $tag, 'areas' => array('User'))), array('class' => 'btn btn-default btn-xs tag')); ?>
            <?php } ?>
        </div>
        <!-- end: tags for user skills -->

    </div>
<?php else : ?>
<p><?= CHtml::encode($user->displayName); ?> has not defined any interests yet.</p>

<?php endif; ?>