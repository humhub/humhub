<?php if ($user->tags) : ?>
    <div id="user-tags-panel" class="panel panel-default" style="position: relative;">

        <!-- Display panel menu widget -->
        <?php $this->widget('application.widgets.PanelMenuWidget', array('id' => 'user-tags-panel')); ?>

        <div class="panel-heading"><?php echo Yii::t('UserModule.widgets_views_userTags', '<strong>User</strong> tags'); ?></div>
        <div class="panel-body">


            <!-- start: tags for user skills -->
            <div class="tags">
                <?php foreach ($user->getTags() as $tag) { ?>
                    <?php echo HHtml::link($tag, $this->createUrl('//directory/directory/members', array('keyword' => 'tags:' . $tag, 'areas' => array('User'))), array('class' => 'btn btn-default btn-xs tag')); ?>
                <?php } ?>
            </div>
            <!-- end: tags for user skills -->

        </div>
    </div>
<?php endif; ?>

<script type="text/javascript">
    function toggleUp() {
        $('.pups').slideUp("fast", function () {
            // Animation complete.
            $('#collapse').hide();
            $('#expand').show();
        });
    }

    function toggleDown() {
        $('.pups').slideDown("fast", function () {
            // Animation complete.
            $('#expand').hide();
            $('#collapse').show();
        });
    }
</script>