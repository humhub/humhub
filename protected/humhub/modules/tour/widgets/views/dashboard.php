<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\content\components\ContentContainerSettingsManager;
use humhub\modules\tour\assets\TourAsset;
use humhub\modules\tour\TourConfig;
use humhub\widgets\Icon;
use humhub\widgets\bootstrap\Link;
use humhub\widgets\menu\MenuLink;
use humhub\widgets\SidebarCard;
use yii\helpers\Url;

TourAsset::register($this);

/**
 * @var View $this
 * @var ContentContainerSettingsManager $settingsManager
 * @var bool $showWelcome
 */

$removeEntry = new MenuLink([
    'link' => Link::to(
        Yii::t('TourModule.base', '<strong>Remove</strong> tour panel'),
        ["/tour/tour/hide-panel", "ajax" => 1],
    )
        ->encodeLabel(false)
        ->icon('eye-slash')
        ->action('tour.hidePanel')
        ->confirm(
            Icon::get('eye-slash') . ' ' . Yii::t('TourModule.base', ' Remove panel'),
            Yii::t('TourModule.base', 'This action will remove the tour panel from your dashboard. You can reactivate it at<br>Account settings {icon} Settings.', ['icon' => Icon::get('caret-right-filled')]),
            Yii::t('TourModule.base', 'Ok'),
            Yii::t('TourModule.base', 'Cancel'),
        ),
]);
?>

<?php SidebarCard::begin([
    'title' => Yii::t('TourModule.base', '<strong>Getting</strong> Started'),
    'encodeTitle' => false,
    'menu' => [$removeEntry],
    'options' => ['id' => 'getting-started-panel', 'class' => 'panel-tour'],
]) ?>
        <p>
            <?= Yii::t('TourModule.base', 'Get to know your way around the site\'s most important features with the following guides:') ?>
        </p>

        <ul class="tour-list">
            <?php foreach (TourConfig::get() as $config): ?>
                <?php $isCompleted = $settingsManager->get(TourConfig::getTourId($config)); ?>
                <li id="tour-panel-<?= TourConfig::getTourId($config) ?>"<?= $isCompleted ? ' class="completed"' : '' ?>>
                    <a href="<?= TourConfig::getStartUrl($config) ?>" class="<?= $isCompleted ? 'link-secondary' : 'link-accent' ?>" data-pjax-prevent>
                        <?= Icon::get('play-circle-o')->class('icon-before-label') ?><?= TourConfig::getTitle($config) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
<?php SidebarCard::end() ?>

<?php if ($showWelcome): ?>
    <script <?= Html::nonce() ?>>
        $(document).on('humhub:ready', function () {
            humhub.modules.ui.modal.global.load("<?= Url::to(['/tour/tour/welcome']) ?>");
        });
    </script>
<?php endif; ?>
