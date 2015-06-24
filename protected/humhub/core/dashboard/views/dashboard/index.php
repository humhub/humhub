<div class="container">
    <div class="row">
        <div class="col-md-8">
            <?php
            if ($showProfilePostForm) {
                $this->widget('application.modules_core.post.widgets.PostFormWidget', array(
                    'contentContainer' => Yii::app()->user->model
                ));
            }
            ?>

            <?php
            $this->widget('application.modules_core.wall.widgets.StreamWidget', array(
                'streamAction' => '//dashboard/dashboard/stream',
                'showFilters' => false,
                'messageStreamEmpty' => Yii::t('DashboardModule.views_dashboard_index', '<b>Your dashboard is empty!</b><br>Post something on your profile or join some spaces!'),
            ));
            ?>
        </div>
        <div class="col-md-4">
            <?php
            $this->widget('application.modules_core.dashboard.widgets.DashboardSidebarWidget', array(
                'widgets' => array(
                    array('application.modules_core.activity.widgets.ActivityStreamWidget', array('streamAction' => '//dashboard/dashboard/stream'), array('sortOrder' => 10)),
                )
            ));
            ?>
        </div>
    </div>
</div>
