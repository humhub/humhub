<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\content\components\ContentContainerSettingsManager;
use humhub\modules\tour\assets\TourAsset;
use humhub\modules\tour\TourConfig;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\widgets\PanelMenu;
use yii\helpers\Url;

TourAsset::register($this);

/**
 * @var View $this
 * @var ContentContainerSettingsManager $settingsManager
 * @var bool $showWelcome
 */

$title = Yii::t('TourModule.base', '<strong>Getting</strong> Started');
?>

<div class="panel panel-default panel-tour" id="getting-started-panel">
    <?= PanelMenu::widget(['panelLabel' => $title]) ?>

    <div class="panel-heading">
        <?= $title ?>
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
