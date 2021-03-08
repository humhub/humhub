<?php

use humhub\modules\friendship\widgets\FriendsPanel;
use humhub\modules\user\widgets\ProfileSidebar;
use humhub\modules\user\widgets\UserFollower;
use humhub\modules\user\widgets\UserSpaces;
use humhub\modules\xcoin\widgets\UserCoin;
use humhub\modules\xcoin\widgets\UserProfileOfferNeed;
use humhub\modules\xcoin\widgets\UserExperience;
use humhub\modules\xcoin\widgets\ProjectPortfolio;
use humhub\modules\xcoin\widgets\MarketPlacePortfolio;
use humhub\modules\post\widgets\Form;
use humhub\modules\user\widgets\StreamViewer;
use humhub\modules\xcoin\widgets\MyRecentActivities;
use humhub\modules\activity\widgets\ActivityStreamViewer;
use humhub\modules\tasks\widgets\MyTasks;
use humhub\modules\tasks\widgets\MyTasksUser;

?>
<?php
/*ProjectPortfolio::widget(['user' => $user]); ?>
<?= MyRecentActivities::widget([
    'widgets' => [
        [
            ActivityStreamViewer::class,
            ['streamAction' => '/dashboard/dashboard/activity-stream'],
            ['sortOrder' => 150]
        ]
    ]
]);
**/
?>

<?= UserProfileOfferNeed::widget(['user' => $user]) ?>
<?= UserCoin::widget(['user' => $user, 'cssClass' => 'tabletView']) ?>

<?= UserExperience::widget(['user' => $user, 'htmlOptions' => ['style' => '']]) ?>
<?= MyTasksUser::widget(['user' => $user,'cssClass' => 'tabletViewTasks'])?>

<?=  MarketPlacePortfolio::widget(['user' => $user]); ?>

<?php if (!Yii::$app->user->isGuest) : ?>
    <?= Form::widget(['contentContainer' =>  $user]) ?> 
<?php endif; ?>
<div class="recentPosts panel panel-default">
    <div class="panel-heading">
        <?= Yii::t('UserModule.views_profile_home', '<strong>Recent</strong> posts'); ?>
    </div>
    <div class="panel-body">
        <?= StreamViewer::widget(['contentContainer' => $user]); ?>
    </div>
</div>

<?//= \humhub\widgets\LoaderWidget::widget() ?>

<?php $this->beginBlock('sidebar'); ?>
<?=
ProfileSidebar::widget([
    'user' => $user,
    'widgets' => [
        [UserCoin::class, ['user' => $user], ['sortOrder' => 10]],
        [UserSpaces::class, ['user' => $user], ['sortOrder' => 20]],
        [FriendsPanel::class, ['user' => $user], ['sortOrder' => 30]],
        [UserFollower::class, ['user' => $user], ['sortOrder' => 40]],
        [MyTasksUser::class,['user' => $user],['sortOrder'=>50]],

        

    ]
]);
?>


<?php $this->endBlock(); ?>
