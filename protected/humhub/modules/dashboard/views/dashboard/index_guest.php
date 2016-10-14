<div class="container">
    <div class="row">
        <div class="col-md-8">
            <?= \humhub\modules\dashboard\widgets\DashboardContent::widget(); ?>
        </div>
        <div class="col-md-4 layout-sidebar-container">
            <?php
            echo \humhub\modules\dashboard\widgets\Sidebar::widget([
                'widgets' => [
                    [
                        \humhub\modules\directory\widgets\NewMembers::className(),
                        ['showMoreButton' => true],
                        ['sortOrder' => 300]
                    ],
                    [
                        \humhub\modules\directory\widgets\NewSpaces::className(),
                        ['showMoreButton' => true],
                        ['sortOrder' => 400]
                    ],
                ]
            ]);
            ?>
        </div>
    </div>
</div>
