<?php

use humhub\libs\Html;
use humhub\modules\dashboard\widgets\DashboardContent;
use humhub\modules\dashboard\widgets\Sidebar;
use humhub\modules\directory\widgets\NewMembers;
use humhub\modules\directory\widgets\NewSpaces;
use humhub\widgets\FooterMenu;

?>

<?= Html::beginContainer(); ?>
<div class="row">
    <div class="col-md-8 layout-content-container">
        <?= DashboardContent::widget(); ?>
    </div>
    <div class="col-md-4 layout-sidebar-container">
        <?= Sidebar::widget([
            'widgets' => [
                [
                    NewMembers::class,
                    ['showMoreButton' => true],
                    ['sortOrder' => 300]
                ],
                [
                    NewSpaces::class,
                    ['showMoreButton' => true],
                    ['sortOrder' => 400]
                ],
            ]
        ]);
        ?>
        <?= FooterMenu::widget(['location' => FooterMenu::LOCATION_SIDEBAR]); ?>
    </div>
</div>
<?= Html::endContainer(); ?>
