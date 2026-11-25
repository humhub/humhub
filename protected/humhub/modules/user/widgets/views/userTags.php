<?php

use humhub\helpers\Html;
use humhub\widgets\SidebarCard;
use yii\helpers\Url;

?>
<?php if ($user->hasTags()) : ?>
    <?php SidebarCard::begin([
        'title' => Yii::t('UserModule.base', '<strong>User</strong> tags'),
        'encodeTitle' => false,
        'options' => ['id' => 'user-tags-panel'],
    ]) ?>
            <!-- start: tags for user skills -->
            <ul class="tags list-unstyled d-flex flex-wrap gap-1">
                <?php foreach ($user->getTags() as $tag): ?>
                    <li><?= Html::a(Html::encode($tag), Url::to(['/user/people', 'q' => $tag]), ['class' => 'btn btn-light btn-sm tag']) ?></li>
                <?php endforeach; ?>
            </ul>
            <!-- end: tags for user skills -->
    <?php SidebarCard::end() ?>
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
