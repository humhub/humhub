<?php

use yii\helpers\Url;

?>

<script <?= \humhub\libs\Html::nonce() ?>>
    $(document).one('humhub:ready', function () {
        humhub.require('tour').start(
            {
                name: 'spaces',
                nextUrl: '<?= Yii::$app->user->getIdentity()->createUrl('/user/profile', ['tour' => true]); ?>',
                steps: [
                    {
                        orphan: true,
                        backdrop: true,
                        title: <?= json_encode(Yii::t('TourModule.spaces', '<strong>Space</strong>')); ?>,
                        content: <?= json_encode(Yii::t('TourModule.spaces', "Once you have joined or created a new space you can work on projects, discuss topics or just share information with other users.<br><br>There are various tools to personalize a space, thereby making the work process more productive.")); ?>
                    },
                    {
                        element: ".layout-nav-container .panel",
                        title: <?= json_encode(Yii::t('TourModule.spaces', '<strong>Space</strong> navigation menu')); ?>,
                        content: <?= json_encode(Yii::t('TourModule.spaces', 'This is where you can navigate the space – where you find which modules are active or available for the particular space you are currently in. These could be polls, tasks or notes for example.<br><br>Only the space admin can manage the space\'s modules.')); ?>,
                        placement: "right"
                    },
                    {
                        element: ".dropdown-navigation",
                        title: <?= json_encode(Yii::t('TourModule.spaces', '<strong>Space</strong> preferences')); ?>,
                        content: <?= json_encode(Yii::t('TourModule.spaces', 'This menu is only visible for space admins. Here you can manage your space settings, add/block members and activate/deactivate tools for this space.')); ?>,
                        placement: "bottom"
                    },
                    {
                        element: "#contentFormBody",
                        title: <?= json_encode(Yii::t('TourModule.spaces', '<strong>Writing</strong> posts')); ?>,
                        content: <?= json_encode(Yii::t('TourModule.spaces', 'New posts can be written and posted here.')); ?>,
                        placement: "bottom"
                    },
                    {
                        element: ".wall-entry:eq(0)",
                        title: <?= json_encode(Yii::t('TourModule.spaces', '<strong>Posts</strong>')); ?>,
                        content: <?= json_encode(Yii::t('TourModule.spaces', 'Yours, and other users\' posts will appear here.<br><br>These can then be liked or commented on.')); ?>,
                        placement: "top"
                    },
                    {
                        element: ".panel-activities",
                        title: <?= json_encode(Yii::t('TourModule.spaces', '<strong>Most recent</strong> activities')); ?>,
                        content: <?= json_encode(Yii::t('TourModule.spaces', 'To keep you up to date, other users\' most recent activities in this space will be displayed here.')); ?>,
                        placement: "left"
                    },
                    {
                        element: "#space-members-panel",
                        title: <?= json_encode(Yii::t('TourModule.spaces', '<strong>Space</strong> members')); ?>,
                        content: <?= json_encode(Yii::t('TourModule.spaces', 'All users who are a member of this space will be displayed here.<br /><br />New members can be added by anyone who has been given access rights by the admin.')); ?>,
                        placement: "left"
                    },
                    {
                        orphan: true,
                        backdrop: true,
                        title: <?= json_encode(Yii::t('TourModule.spaces', '<strong>Yay! You\'re done.</strong>')); ?>,
                        content: <?= json_encode(Yii::t('TourModule.spaces', "That's it for the space guide.<br><br>To carry on with the user profile guide, click here: ")); ?> + "<a href='#' data-action-click='tour.next' ><?= Yii::t("TourModule.spaces", "<strong>Profile Guide</strong>"); ?></a><br><br>"
                    }
                ]
            }
        );
    });
</script>
