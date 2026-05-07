<?php

use humhub\modules\user\models\User;
use humhub\modules\user\widgets\Image;
use humhub\widgets\PanelMenu;

/* @var User[] $followers */
/* @var User[] $following */

$followersTile = Yii::t('UserModule.base', '<strong>Followers</strong>');
$followingTile = Yii::t('UserModule.base', '<strong>Following</strong>');
?>
<?php if (count($followers) > 0) : ?>
    <div class="panel panel-default profile-follower-panel" id="profile-follower-panel">

        <!-- Display panel menu widget -->
        <?= PanelMenu::widget([
            'panelId' => 'humhubmodulesuserfollowerwidgetspanel', // Unique ID, as the widget class is the same for both panels
            'panelLabel' => $followersTile,
        ]) ?>

        <div class="panel-heading"><?= $followersTile ?></div>

        <div class="panel-body">
            <div class="d-flex column-gap-2 flex-wrap">
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
    </div>
<?php endif; ?>

<?php if (count($following) > 0) : ?>
    <div class="panel panel-default profile-following-panel" id="profile-following-panel">

        <!-- Display panel menu widget -->
        <?= PanelMenu::widget([
            'panelId' => 'humhubmodulesuserfollowingwidgetspanel', // Unique ID, as the widget class is the same for both panels
            'panelLabel' => $followingTile,
        ]) ?>

        <div class="panel-heading">
            <?= $followingTile ?>
        </div>

        <div class="panel-body">
            <div class="d-flex column-gap-2 flex-wrap">
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
    </div>
<?php endif; ?>
