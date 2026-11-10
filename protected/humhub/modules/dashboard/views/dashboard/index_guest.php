<?php

use humhub\helpers\Html;
use humhub\modules\dashboard\widgets\DashboardContent;
use humhub\modules\dashboard\widgets\Sidebar;
use humhub\modules\space\widgets\NewSpaces;
use humhub\widgets\FooterMenu;

?>

<?= Html::beginContainer(); ?>
<?php /* The page's heading, which the dashboard does not show */ ?>
<h1 class="visually-hidden"><?= Yii::t('DashboardModule.base', 'Dashboard') ?></h1>
<div class="row">
    <div id="layout-content-column" class="col-lg-8 layout-content-container">
        <?= DashboardContent::widget(); ?>
    </div>
    <aside id="layout-sidebar" class="col-lg-4 layout-sidebar-container" aria-label="<?= Html::encode(Yii::t('base', 'Sidebar')) ?>">
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
    </aside>
</div>
<?= Html::endContainer(); ?>
