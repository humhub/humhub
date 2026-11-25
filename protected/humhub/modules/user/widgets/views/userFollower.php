<?php

use humhub\modules\user\models\User;
use humhub\modules\user\widgets\Image;
use humhub\widgets\SidebarCard;

/* @var User[] $followers */
/* @var User[] $following */
?>
<?php if (count($followers) > 0) : ?>
    <?php SidebarCard::begin([
        'title' => Yii::t('UserModule.base', '<strong>Followers</strong>'),
        'encodeTitle' => false,
        'collapseId' => 'humhubmodulesuserfollowerwidgetspanel', // Unique ID, as the widget class is the same for both cards
        'options' => ['id' => 'profile-follower-panel', 'class' => 'profile-follower-panel'],
    ]) ?>
            <div class="d-flex gap-2 flex-wrap">
                <?php foreach ($followers as $follower): ?>
                    <?= Image::widget([
                        'user' => $follower,
                        'width' => 30,
                        'link' => true,
                        'showTooltip' => true,
                    ]) ?>
                <?php endforeach; ?>
            </div>
    <?php SidebarCard::end() ?>
<?php endif; ?>

<?php if (count($following) > 0) : ?>
    <?php SidebarCard::begin([
        'title' => Yii::t('UserModule.base', '<strong>Following</strong>'),
        'encodeTitle' => false,
        'collapseId' => 'humhubmodulesuserfollowingwidgetspanel', // Unique ID, as the widget class is the same for both cards
        'options' => ['id' => 'profile-following-panel', 'class' => 'profile-following-panel'],
    ]) ?>
            <div class="d-flex gap-2 flex-wrap">
                <?php foreach ($following as $followingUser): ?>
                    <?= Image::widget([
                        'user' => $followingUser,
                        'width' => 30,
                        'link' => true,
                        'showTooltip' => true,
                    ]) ?>
                <?php endforeach; ?>
            </div>
    <?php SidebarCard::end() ?>
<?php endif; ?>
