<?php

use humhub\helpers\Html;
use humhub\widgets\PanelMenu;
use yii\helpers\Url;

?>
<?php if ($user->hasTags()) : ?>
    <div id="user-tags-panel" class="panel panel-default" style="position: relative;">

        <?= PanelMenu::widget() ?>

        <div class="panel-heading"><?= Yii::t('UserModule.base', '<strong>User</strong> tags') ?></div>
        <div class="panel-body">
            <!-- start: tags for user skills -->
            <ul class="tags list-unstyled d-flex flex-wrap gap-1">
                <?php foreach ($user->getTags() as $tag): ?>
                    <li><?= Html::a(Html::encode($tag), Url::to(['/user/people', 'keyword' => $tag]), ['class' => 'btn btn-light btn-sm tag']) ?></li>
                <?php endforeach; ?>
            </ul>
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
