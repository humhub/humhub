<?php
$this->pageTitle = Yii::t('DashboardModule.views_dashboard_index', 'Dashboard');
?>
<div class="container">
    <div class="row">
        <div class="col-lg-3 visible-lg">
            <!-- load space chooser widget -->
            <?php echo \humhub\modules\space\widgets\Chooser::widget(); ?>
        </div>
        <div class="col-lg-6 col-md-6">
            <?php
            if ($showProfilePostForm) {
                echo \humhub\modules\post\widgets\Form::widget(['contentContainer' => \Yii::$app->user->getIdentity()]);
            }
            ?>

            <?php
            echo \humhub\modules\content\widgets\Stream::widget([
                'streamAction' => '//dashboard/dashboard/stream',
                'showFilters' => false,
                'messageStreamEmpty' => Yii::t('DashboardModule.views_dashboard_index', '<b>Your dashboard is empty!</b><br>Post something on your profile or join some spaces!'),
            ]);
            ?>
        </div>
        <div class="col-lg-3 col-md-6">
            <?php
            echo \humhub\modules\dashboard\widgets\Sidebar::widget(['widgets' => [
                    [\humhub\modules\activity\widgets\Stream::className(), ['streamAction' => '/dashboard/dashboard/stream'], ['sortOrder' => 10]]
            ]]);
            ?>
        </div>
    </div>
</div>
