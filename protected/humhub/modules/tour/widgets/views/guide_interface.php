<?php

use yii\helpers\Url;

/* @var $this \humhub\modules\ui\view\components\View */
?>

<script type="text/javascript">
    $(document).one('humhub:ready', function () {
        humhub.require('tour').start(
            {
                name: 'interface',
                nextUrl: '<?= Url::to(['/tour/tour/start-space-tour'])?>',
                steps: [
                    {
                        orphan: true,
                        backdrop: true,
                        title: <?= json_encode(Yii::t('TourModule.widgets_views_guide_interface', '<strong>Dashboard</strong>')); ?>,
                        content: <?= json_encode(Yii::t('TourModule.widgets_views_guide_interface', "This is your dashboard.<br><br>Any new activities or posts that might interest you will be displayed here.")); ?>
                    },
                    {
                        element: "#icon-notifications",
                        title: <?= json_encode(Yii::t('TourModule.widgets_views_index', '<strong>Notifications</strong>')); ?>,
                        content: <?= json_encode(Yii::t('TourModule.widgets_views_index', 'Don\'t lose track of things!<br /><br />This icon will keep you informed of activities and posts that concern you directly.')); ?>,
                        placement: "bottom"
                    },
                    {
                        element: ".dropdown.account",
                        title: <?= json_encode(Yii::t('TourModule.widgets_views_index', '<strong>Account</strong> Menu')); ?>,
                        content: <?= json_encode(Yii::t('TourModule.widgets_views_index', 'The account menu gives you access to your private settings and allows you to manage your public profile.')); ?>,
                        placement: "bottom"
                    },
                    {
                        element: "#space-menu",
                        title: <?= json_encode(Yii::t('TourModule.widgets_views_index', '<strong>Space</strong> Menu')); ?>,
                        content: <?= json_encode(Yii::t('TourModule.widgets_views_index', 'This is the most important menu and will probably be the one you use most often!<br><br>Access all the spaces you have joined and create new spaces here.<br><br>The next guide will show you how:')); ?> +"<br><br><a href='#' data-action-click='tour.next'><?=Yii::t("TourModule.widgets_views_index", "<strong>Start</strong> space guide"); ?></a><br><br>",
                        placement: "bottom"
                    }
                ]
            }
        );
    });
</script>