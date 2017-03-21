<?php

use yii\helpers\Url;
?>
<div class="panel panel-default panel-tour" id="getting-started-panel">
    <?php
    // Temporary workaround till panel widget rewrite in 0.10 verion
    $removeOptionHtml = "<li>" . \humhub\widgets\ModalConfirm::widget(array(
                'uniqueID' => 'hide-panel-button',
                'title' => '<strong>Remove</strong> tour panel',
                'message' => 'This action will remove the tour panel from your dashboard. You can reactivate it at<br>Account settings <i class="fa fa-caret-right"></i> Settings.',
                'buttonTrue' => 'Ok',
                'buttonFalse' => 'Cancel',
                'linkContent' => '<i class="fa fa-eye-slash"></i> ' . Yii::t('TourModule.widgets_views_tourPanel', ' Remove panel'),
                'linkHref' => Url::to(["/tour/tour/hide-panel", "ajax" => 1]),
                'confirmJS' => '$(".panel-tour").slideToggle("slow")'
    ), true) . "</li>";
    ?>

    <!-- Display panel menu widget -->
    <?= \humhub\widgets\PanelMenu::widget(array('id' => 'getting-started-panel', 'extraMenus' => $removeOptionHtml)); ?>

    <div class="panel-heading">
        <?= Yii::t('TourModule.widgets_views_tourPanel', '<strong>Getting</strong> Started'); ?>
    </div>
    <div class="panel-body">
        <p><?= Yii::t('TourModule.widgets_views_tourPanel', 'Get to know your way around the site\'s most important features with the following guides:'); ?></p>

        <ul class="tour-list">
            <li id="interface_entry" class="<?php if ($interface == 1) : ?>completed<?php endif; ?>">
                <a href="<?= Url::to(['/dashboard/dashboard', 'tour' => true]); ?>">
                    <i class="fa fa-play-circle-o"></i><?= Yii::t('TourModule.widgets_views_tourPanel', '<strong>Guide:</strong> Overview'); ?>
                </a>
            </li>
            <li class="<?php if ($spaces == 1) : ?>completed<?php endif; ?>">
                <a id="interface-tour-link" href="<?= Url::to(['/tour/tour/start-space-tour']); ?>">
                    <i class="fa fa-play-circle-o"></i><?= Yii::t('TourModule.widgets_views_tourPanel', '<strong>Guide:</strong> Spaces'); ?>
                </a>
            </li>
            <li class="<?php if ($profile == 1) : ?>completed<?php endif; ?>"><a
                    href="<?= Yii::$app->user->getIdentity()->createUrl('//user/profile', array('tour' => 'true')); ?>"><i
                        class="fa fa-play-circle-o"></i><?= Yii::t('TourModule.widgets_views_tourPanel', '<strong>Guide:</strong> User profile'); ?>
                </a></li>
            <?php if (Yii::$app->user->isAdmin() == true) : ?>
                <li class="<?php if ($administration == 1) : ?>completed<?php endif; ?>">
                    <a href="<?= Url::to(['/admin/module/list-online', 'tour' => 'true']); ?>">
                        <i class="fa fa-play-circle-o"></i><?= Yii::t('TourModule.widgets_views_tourPanel', '<strong>Guide:</strong> Administration (Modules)'); ?>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<?php if ($showWelcome) : ?>
    <script>

        $(document).ready(function () {

            $('#globalModal').modal({
                show: true,
                backdrop: 'static'
            })

            $.ajax({
                url: "<?= Url::to(['/tour/tour/welcome']); ?>",
                context: document.body
            }).done(function (html) {
                $('#globalModal').html(html);
            });

        });

    </script>
<?php endif; ?>