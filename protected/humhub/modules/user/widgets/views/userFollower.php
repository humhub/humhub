<?php

use humhub\modules\user\models\User;
use humhub\modules\user\widgets\Image;
use humhub\widgets\PanelMenu;

/* @var User[] $followers */
/* @var User[] $following */
?>
<?php if (count($followers) > 0) : ?>
    <div class="card content-card profile-follower-panel" id="profile-follower-panel">

        <!-- Display panel menu widget -->
        <?= PanelMenu::widget([
            'collapseId' => 'humhubmodulesuserfollowerwidgetspanel', // Unique ID, as the widget class is the same for both panels
        ]) ?>

        <div class="card-header"><div class="card-title"><?= Yii::t('UserModule.base', '<strong>Followers</strong>') ?></div></div>

        <div class="card-body">
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
        </div>
    </div>
<?php endif; ?>

<?php if (count($following) > 0) : ?>
    <div class="card content-card profile-following-panel" id="profile-following-panel">

        <!-- Display panel menu widget -->
        <?= PanelMenu::widget([
            'collapseId' => 'humhubmodulesuserfollowingwidgetspanel', // Unique ID, as the widget class is the same for both panels
        ]) ?>

        <div class="card-header">
            <div class="card-title"><?= Yii::t('UserModule.base', '<strong>Following</strong>') ?></div>
        </div>

        <div class="card-body">
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
        </div>
    </div>
<?php endif; ?>
