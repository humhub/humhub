<div class="panel panel-default members" id="new-people-panel">

    <!-- Display panel menu widget -->
    <?php $this->widget('application.widgets.PanelMenuWidget', array('id' => 'new-people-panel')); ?>

    <div class="panel-heading">
        <?php echo Yii::t('DirectoryModule.widgets_views_memberStats', '<strong>New</strong> people'); ?>
    </div>
    <div class="panel-body">
        <?php foreach ($newUsers as $user) : ?>
            <a href="<?php echo $user->getProfileUrl(); ?>">
                <img src="<?php echo $user->getProfileImage()->getUrl(); ?>" class="img-rounded tt img_margin"
                     height="40" width="40" alt="40x40" data-src="holder.js/40x40" style="width: 40px; height: 40px;"
                     data-toggle="tooltip" data-placement="top" title=""
                     data-original-title="<strong><?php echo CHtml::encode($user->displayName); ?></strong><br><?php echo CHtml::encode($user->profile->title); ?>">
            </a>
        <?php endforeach; ?>

        <?php if ($showMoreButton): ?>
            <br />
            <br />
            <?php echo HHtml::link(Yii::t('DirectoryModule.widgets_views_newMembers', 'See all'), array('//directory/directory/members'), array('class' => 'btn btn-xl btn-primary')); ?>
        <?php endif; ?>

    </div>
</div>