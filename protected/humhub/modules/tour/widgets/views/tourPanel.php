<?php

use humhub\libs\Html;
use humhub\modules\tour\assets\TourAsset;
use humhub\widgets\PanelMenu;
use yii\helpers\Url;

TourAsset::register($this);

?>
<div class="panel panel-default panel-tour" id="getting-started-panel">
    <?php

    $removeOptionHtml = Html::tag(
        'li',
        Html::a(
            Yii::t('TourModule.base', '<strong>Remove</strong> tour panel'),
            Url::to(["/tour/tour/hide-panel", "ajax" => 1]),
            [
                'data' => [
                    'action-click' => 'tour.hidePanel',
                    'action-confirm-header' => Html::tag('i', '', ['class' => ['fa', 'fa-eye-slash']]) . Yii::t('TourModule.base', ' Remove panel'),
                    'action-confirm' => Yii::t('TourModule.base', 'This action will remove the tour panel from your dashboard. You can reactivate it at<br>Account settings <i class="fa fa-caret-right"></i> Settings.'),
                    'action-confirm-text' => Yii::t('TourModule.base', 'Ok'),
                    'action-cancel-text' => Yii::t('TourModule.base', 'Cancel'),
                ],
            ]
        )
    );

    ?>

    <!-- Display panel menu widget -->
    <?php echo PanelMenu::widget(['id' => 'getting-started-panel', 'extraMenus' => $removeOptionHtml]); ?>

    <div class="panel-heading">
        <?php echo Yii::t('TourModule.base', '<strong>Getting</strong> Started'); ?>
    </div>
    <div class="panel-body">
        <p>
            <?php echo Yii::t('TourModule.base', 'Get to know your way around the site\'s most important features with the following guides:'); ?>
        </p>

        <ul class="tour-list">
            <li id="interface_entry" class="<?php if ($interface == 1) : ?>completed<?php endif; ?>">
                <a href="<?php echo Url::to(['/dashboard/dashboard', 'tour' => true]); ?>" data-pjax-prevent>
                    <i class="fa fa-play-circle-o"></i><?= Yii::t('TourModule.base', '<strong>Guide:</strong> Overview'); ?>
                </a>
            </li>
            <li class="<?php if ($spaces == 1) : ?>completed<?php endif; ?>">
                <a id="interface-tour-link" href="<?php echo Url::to(['/tour/tour/start-space-tour']); ?>"
                   data-pjax-prevent>
                    <i class="fa fa-play-circle-o"></i><?php echo Yii::t('TourModule.base', '<strong>Guide:</strong> Spaces'); ?>
                </a>
            </li>
            <li class="<?php if ($profile == 1) : ?>completed<?php endif; ?>">
                <a href="<?php echo Yii::$app->user->getIdentity()->createUrl('//user/profile/home', ['tour' => 'true']); ?>"
                   data-pjax-prevent>
                    <i class="fa fa-play-circle-o"></i><?php echo Yii::t('TourModule.base', '<strong>Guide:</strong> User profile'); ?>
                </a>
            </li>
            <?php if (Yii::$app->user->isAdmin() == true) : ?>
                <li class="<?php if ($administration == 1) : ?>completed<?php endif; ?>">
                    <a href="<?php echo Url::to(['/admin/module/list', 'tour' => 'true']); ?>" data-pjax-prevent>
                        <i class="fa fa-play-circle-o"></i><?php echo Yii::t('TourModule.base', '<strong>Guide:</strong> Administration (Modules)'); ?>
                    </a>
                </li>
            <?php endif; ?>
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
