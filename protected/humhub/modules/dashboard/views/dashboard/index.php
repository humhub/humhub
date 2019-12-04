<?php
/**
 * @var \humhub\modules\user\models\User $contentContainer
 * @var bool $showProfilePostForm
 */

use humhub\modules\activity\widgets\ActivityStreamViewer;
use humhub\modules\dashboard\widgets\DashboardContent;
use humhub\modules\dashboard\widgets\Sidebar;
use humhub\widgets\FooterMenu;
use humhub\libs\Html;

?>

<?= Html::beginContainer(); ?>
<div class="row">
    <div class="col-md-8 layout-content-container">
        <?= DashboardContent::widget([
            'contentContainer' => $contentContainer,
            'showProfilePostForm' => $showProfilePostForm
        ]);
        ?>
    </div>
    <div class="col-md-4 layout-sidebar-container">
        <?= Sidebar::widget([
            'widgets' => [
                [
                    ActivityStreamViewer::class,
                    ['streamAction' => '/dashboard/dashboard/activity-stream'],
                    ['sortOrder' => 150]
                ]
            ]
        ]);
        ?>
        <?= FooterMenu::widget(['location' => FooterMenu::LOCATION_SIDEBAR]); ?>
    </div>
</div>
<?= Html::endContainer(); ?>
