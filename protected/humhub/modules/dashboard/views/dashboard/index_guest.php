<?php

use humhub\helpers\Html;
use humhub\modules\dashboard\widgets\DashboardContent;
use humhub\modules\dashboard\widgets\Sidebar;
use humhub\modules\space\widgets\NewSpaces;
use humhub\widgets\FooterMenu;

?>

<?= Html::beginContainer(); ?>
<div class="row">
    <div class="col-lg-8 layout-content-container">
        <?= DashboardContent::widget(); ?>
    </div>
    <div class="col-lg-4 layout-sidebar-container">
        <?= Sidebar::widget([
            'widgets' => [
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
