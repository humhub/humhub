<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\content\components\ContentContainerSettingsManager;
use humhub\modules\tour\assets\TourAsset;
use humhub\modules\tour\TourConfig;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\widgets\bootstrap\Link;
use humhub\widgets\PanelMenu;
use yii\helpers\Url;

TourAsset::register($this);

/**
 * @var View $this
 * @var ContentContainerSettingsManager $settingsManager
 * @var bool $showWelcome
 */

$removeOptionHtml = Html::tag(
    'li',
    Link::to(Yii::t('TourModule.base', '<strong>Remove</strong> tour panel'))
        ->link(["/tour/tour/hide-panel", "ajax" => 1])
        ->icon('eye-slash')
        ->action('tour.hidePanel')
        ->confirm(
            Icon::get('eye-slash') . ' ' . Yii::t('TourModule.base', ' Remove panel'),
            Yii::t('TourModule.base', 'This action will remove the tour panel from your dashboard. You can reactivate it at<br>Account settings <i class="fa fa-caret-right"></i> Settings.'),
            Yii::t('TourModule.base', 'Ok'),
            Yii::t('TourModule.base', 'Cancel'),
        )
        ->cssClass(['btn', 'dropdown-item']),
);
?>

<div class="panel panel-default panel-tour" id="getting-started-panel">
    <?= PanelMenu::widget([
        'extraMenus' => $removeOptionHtml,
    ]) ?>

    <div class="panel-heading">
        <?= Yii::t('TourModule.base', '<strong>Getting</strong> Started') ?>
    </div>

    <div class="panel-body">
        <p>
            <?= Yii::t('TourModule.base', 'Get to know your way around the site\'s most important features with the following guides:') ?>
        </p>

        <ul class="tour-list">
            <?php foreach (TourConfig::get() as $config): ?>
                <?php $isCompleted = $settingsManager->get(TourConfig::getTourId($config)); ?>
                <li id="tour-panel-<?= TourConfig::getTourId($config) ?>"<?= $isCompleted ? ' class="completed"' : '' ?>>
                    <a href="<?= TourConfig::getStartUrl($config) ?>" class="<?= $isCompleted ? 'link-secondary' : 'link-accent' ?>" data-pjax-prevent>
                        <?= Icon::get('play-circle-o') ?> <?= TourConfig::getTitle($config) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<?php if ($showWelcome): ?>
    <script <?= Html::nonce() ?>>
        $(document).on('humhub:ready', function () {
            humhub.modules.ui.modal.global.load("<?= Url::to(['/tour/tour/welcome']) ?>");
        });
    </script>
<?php endif; ?>
