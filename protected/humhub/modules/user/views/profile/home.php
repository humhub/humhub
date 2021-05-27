<?php

use humhub\modules\friendship\widgets\FriendsPanel;
use humhub\modules\post\widgets\Form;
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\ProfileSidebar;
use humhub\modules\user\widgets\StreamViewer;
use humhub\modules\user\widgets\UserFollower;
use humhub\modules\user\widgets\UserSpaces;
use humhub\modules\user\widgets\UserTags;

/* @var $user User */
/* @var $isSingleContentRequest bool */
?>

<div data-stream-create-content="stream.wall.WallStream"<?php if ($isSingleContentRequest) : ?> style="display:none"<?php endif; ?>>
    <?= Form::widget(['contentContainer' => $user]); ?>
</div>

<?= StreamViewer::widget(['contentContainer' => $user]); ?>

<?php $this->beginBlock('sidebar'); ?>
<?=
ProfileSidebar::widget([
    'user' => $user,
    'widgets' => [
        [UserTags::class, ['user' => $user], ['sortOrder' => 10]],
        [UserSpaces::class, ['user' => $user], ['sortOrder' => 20]],
        [FriendsPanel::class, ['user' => $user], ['sortOrder' => 30]],
        [UserFollower::class, ['user' => $user], ['sortOrder' => 40]],
    ]
]);
?>
<?php $this->endBlock(); ?>
