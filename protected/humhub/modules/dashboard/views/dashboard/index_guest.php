<div class="container">
    <div class="row">
        <div class="col-md-8">
            <?php
            echo \humhub\modules\content\widgets\Stream::widget([
                'streamAction' => '//dashboard/dashboard/stream',
                'showFilters' => false,
                'messageStreamEmpty' => Yii::t('DashboardModule.views_dashboard_index_guest', '<b>No public contents to display found!</b>'),
            ]);
            ?>

        </div>
        <div class="col-md-4 layout-sidebar-container">
            <?php
            echo \humhub\modules\dashboard\widgets\Sidebar::widget(['widgets' => [
                    [\humhub\modules\directory\widgets\NewMembers::className(), ['showMoreButton' => true], ['sortOrder' => 300]],
                    [\humhub\modules\directory\widgets\NewSpaces::className(), ['showMoreButton' => true], ['sortOrder' => 400]],
            ]]);
            ?>
        </div>
    </div>
</div>
