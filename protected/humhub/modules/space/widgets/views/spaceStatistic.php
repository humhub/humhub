<div id="space-statistic">
    <div class="pull-left entry">
        <span class="count"><?php echo $postCount; ?></span><br>
        <span class="title"><?php echo Yii::t('SpaceModule.widgets_views_profileHeader', 'Posts'); ?></span>
    </div>

    <a href="<?= $space->createUrl('/space/membership/members-list'); ?>" data-target="#globalModal">
        <div class="pull-left entry">
            <span class="count"><?php echo $space->getMemberships()->count(); ?></span><br>
            <span class="title"><?php echo Yii::t('SpaceModule.widgets_views_profileHeader', 'Members'); ?></span>
        </div>
    </a>

    <a href="<?= $space->createUrl('/space/space/follower-list'); ?>" data-target="#globalModal">
        <div class="pull-left entry">
            <span class="count"><?php echo $space->getFollowerCount(); ?></span><br>
            <span class="title"><?php echo Yii::t('SpaceModule.widgets_views_profileHeader', 'Followers'); ?></span>
        </div>
    </a>
</div>