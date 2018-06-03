<?php

use humhub\modules\user\widgets\ProfileSidebar;
?>
<div class="row profile-content">
    <div class="col-md-8 layout-content-container">
        <?= $content ?>
    </div>
    <div class="col-md-4 layout-sidebar-container">
        <?=
        ProfileSidebar::widget([
            'user' => $user,
            'widgets' => [
                [\humhub\modules\user\widgets\UserTags::className(), ['user' => $user], ['sortOrder' => 10]],
                [\humhub\modules\user\widgets\UserSpaces::className(), ['user' => $user], ['sortOrder' => 20]],
                [\humhub\modules\friendship\widgets\FriendsPanel::className(), ['user' => $user], ['sortOrder' => 30]],
                [\humhub\modules\user\widgets\UserFollower::className(), ['user' => $user], ['sortOrder' => 40]],
            ]
        ]);
        ?>
    </div>
</div>