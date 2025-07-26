<?php

use humhub\helpers\Html;
use humhub\widgets\PanelMenu;
use yii\helpers\Url;

?>
<?php if ($user->hasTags()) : ?>
    <div id="user-tags-panel" class="panel panel-default" style="position: relative;">

        <?= PanelMenu::widget(['collapseId' => 'user-tags-panel-body']) ?>

        <div class="panel-heading"><?= Yii::t('UserModule.base', '<strong>User</strong> tags') ?></div>
        <div class="panel-body collapse" id="user-tags-panel-body">
            <!-- start: tags for user skills -->
            <div class="tags">
                <?php foreach ($user->getTags() as $tag): ?>
                    <?= Html::a(Html::encode($tag), Url::to(['/user/people', 'keyword' => $tag]), ['class' => 'btn btn-light btn-sm tag']) ?>
                <?php endforeach; ?>
            </div>
            <!-- end: tags for user skills -->

        </div>
    </div>
    <script <?= Html::nonce() ?>>
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
