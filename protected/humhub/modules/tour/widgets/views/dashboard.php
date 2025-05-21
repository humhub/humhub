<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\content\components\ContentContainerSettingsManager;
use humhub\modules\tour\assets\TourAsset;
use humhub\modules\tour\models\TourParams;
use humhub\modules\ui\icon\widgets\Icon;
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
    Html::a(
        Yii::t('TourModule.base', '<strong>Remove</strong> tour panel'),
        Url::to(["/tour/tour/hide-panel", "ajax" => 1]),
        [
            'data' => [
                'action-click' => 'tour.hidePanel',
                'action-confirm-header' => Icon::get('eye-slash') . ' ' . Yii::t('TourModule.base', ' Remove panel'),
                'action-confirm' => Yii::t('TourModule.base', 'This action will remove the tour panel from your dashboard. You can reactivate it at<br>Account settings <i class="fa fa-caret-right"></i> Settings.'),
                'action-confirm-text' => Yii::t('TourModule.base', 'Ok'),
                'action-cancel-text' => Yii::t('TourModule.base', 'Cancel'),
            ],
        ],
    ),
);
?>

<div class="panel panel-default panel-tour" id="getting-started-panel">
    <?= PanelMenu::widget(['id' => 'getting-started-panel', 'extraMenus' => $removeOptionHtml]) ?>

    <div class="panel-heading">
        <?= Yii::t('TourModule.base', '<strong>Getting</strong> Started') ?>
    </div>
    <div class="panel-body">
        <p>
            <?= Yii::t('TourModule.base', 'Get to know your way around the site\'s most important features with the following guides:') ?>
        </p>

        <ul class="tour-list">
            <?php foreach (TourParams::get() as $params): ?>
                <?php if (isset($params[TourParams::KEY_PAGE], $params[TourParams::KEY_URL], $params[TourParams::KEY_TITLE])): ?>
                    <li id="tour-panel-<?= $params[TourParams::KEY_PAGE] ?>"<?= $settingsManager->get($params[TourParams::KEY_PAGE]) ? ' class="completed"' : '' ?>>
                        <a href="<?= $params[TourParams::KEY_URL] ?>" data-pjax-prevent>
                            <?= Icon::get('play-circle-o') ?> <?= $params[TourParams::KEY_TITLE] ?>
                        </a>
                    </li>
                <?php endif; ?>
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
