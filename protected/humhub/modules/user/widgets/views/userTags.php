<?php

use yii\helpers\Html;
use yii\helpers\Url;
?>
<?php if ($user->hasTags()) : ?>
    <div id="user-tags-panel" class="card card-default" style="position: relative;">

        <?php echo \humhub\widgets\PanelMenu::widget(['id' => 'user-tags-panel']); ?>

        <div class="card-header"><?php echo Yii::t('UserModule.base', '<strong>User</strong> tags'); ?></div>
        <div class="card-body">
            <!-- start: tags for user skills -->
            <div class="tags">
                <?php foreach ($user->getTags() as $tag): ?>
                    <?php echo Html::a(Html::encode($tag), Url::to(['/user/people', 'keyword' => $tag]), ['class' => 'btn btn-outline-secondary btn-sm tag']); ?>
                <?php endforeach; ?>
            </div>
            <!-- end: tags for user skills -->

        </div>
    </div>
    <script <?= \humhub\libs\Html::nonce() ?>>
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
<?php endif; ?>
