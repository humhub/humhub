<?php

use humhub\modules\dashboard\widgets\DashboardContent;
use humhub\modules\dashboard\widgets\Sidebar;
use humhub\modules\directory\widgets\NewMembers;
use humhub\modules\directory\widgets\NewSpaces;
?>

<div class="container">
    <div class="row">
        <div class="col-md-8">
            <?= DashboardContent::widget(); ?>
        </div>
        <div class="col-md-4 layout-sidebar-container">
            <?= Sidebar::widget([
                'widgets' => [
                    [
                        NewMembers::className(),
                        ['showMoreButton' => true],
                        ['sortOrder' => 300]
                    ],
                    [
                        NewSpaces::className(),
                        ['showMoreButton' => true],
                        ['sortOrder' => 400]
                    ],
                ]
            ]);
            ?>
        </div>
    </div>
</div>
