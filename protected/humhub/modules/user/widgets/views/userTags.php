<?php

use yii\helpers\Html;
use yii\helpers\Url;
?>
<?php if ($user->hasTags()) : ?>
    <div id="user-tags-panel" class="panel panel-default" style="position: relative;">

        <?php echo \humhub\widgets\PanelMenu::widget(['id' => 'user-tags-panel']); ?>

        <div class="panel-heading"><?php echo Yii::t('UserModule.widgets_views_userTags', '<strong>User</strong> tags'); ?></div>
        <div class="panel-body">
            <!-- start: tags for user skills -->
            <div class="tags">
                <?php foreach ($user->getTags() as $tag): ?>
                    <?php echo Html::a(Html::encode($tag), Url::to(['/directory/directory/members', 'keyword' => $tag]), array('class' => 'btn btn-default btn-xs tag')); ?>
                <?php endforeach; ?>
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