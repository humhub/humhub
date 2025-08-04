<?php

use humhub\modules\user\models\User;
use humhub\modules\user\widgets\Image;
use humhub\widgets\PanelMenu;

/* @var User[] $followers */
/* @var User[] $following */
?>
<?php if (count($followers) > 0) : ?>
    <div class="panel panel-default follower" id="profile-follower-panel">

        <!-- Display panel menu widget -->
        <?= PanelMenu::widget() ?>

        <div class="panel-heading"><?php echo Yii::t('UserModule.base', '<strong>Followers</strong>'); ?></div>

        <div class="panel-body d-flex column-gap-2 flex-wrap">
            <?php foreach ($followers as $follower): ?>
                <?= Image::widget([
                    'user' => $follower,
                    'width' => 30,
                    'link' => true,
                    'showTooltip' => true,
                ]) ?>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?php if (count($following) > 0) : ?>
    <div class="panel panel-default follower" id="profile-following-panel">

        <!-- Display panel menu widget -->
        <?= PanelMenu::widget() ?>

        <div class="panel-heading">
            <?= Yii::t('UserModule.base', '<strong>Following</strong>') ?>
        </div>

        <div class="panel-body d-flex column-gap-2 flex-wrap">
            <?php foreach ($following as $followingUser): ?>
                <?= Image::widget([
                    'user' => $followingUser,
                    'width' => 30,
                    'link' => true,
                    'showTooltip' => true,
                ]) ?>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
