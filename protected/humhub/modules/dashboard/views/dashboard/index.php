<?php

/**
 * @var \humhub\modules\user\models\User $contentContainer
 * @var bool $showProfilePostForm
 */

use humhub\helpers\Html;
use humhub\modules\activity\widgets\ActivityBox;
use humhub\modules\dashboard\widgets\DashboardContent;
use humhub\modules\dashboard\widgets\Sidebar;
use humhub\widgets\FooterMenu;

?>

<?= Html::beginContainer() ?>
<div class="row">
    <div class="col-lg-8 layout-content-container">
        <?= DashboardContent::widget([
            'contentContainer' => $contentContainer,
            'showProfilePostForm' => $showProfilePostForm
        ]);
        ?>
    </div>
    <div class="col-lg-4 layout-sidebar-container">
        <?= Sidebar::widget([
            'widgets' => [
                [
                    ActivityBox::class, [],
                    ['sortOrder' => 150]
                ]
            ]
        ]);
        ?>
        <?= FooterMenu::widget(['location' => FooterMenu::LOCATION_SIDEBAR]); ?>
    </div>
</div>
<?= Html::endContainer() ?>
