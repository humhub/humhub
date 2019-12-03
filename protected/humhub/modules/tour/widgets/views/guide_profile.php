<?php

use yii\helpers\Url;

/* @var $this \humhub\modules\ui\view\components\View */

$isAmind = Yii::$app->user->isAdmin();
$nextUrl = $isAmind ? Url::to(['/marketplace/browse', 'tour' => 'true']) : '';
?>

<script <?= \humhub\libs\Html::nonce() ?>>
    $(document).one('humhub:ready', function () {
        humhub.require('tour').start(
            {
                name: 'profile',
                nextUrl: '<?= $nextUrl; ?>',
                steps:[
                    {
                        orphan: true,
                        backdrop: true,
                        title: <?= json_encode(Yii::t('TourModule.profile', '<strong>User profile</strong>')); ?>,
                        content: <?= json_encode(Yii::t('TourModule.profile', "This is your public user profile, which can be seen by any registered user.")); ?>
                    },
                    {
                        element: ".profile-user-photo-container",
                        title: <?= json_encode(Yii::t('TourModule.profile', '<strong>Profile</strong> photo')); ?>,
                        content: <?= json_encode(Yii::t('TourModule.profile', 'Upload a new profile photo by simply clicking here or by drag&drop. Do just the same for updating your cover photo.')); ?>,
                        placement: "right"
                    },
                    {
                        element: ".edit-account",
                        title: <?= json_encode(Yii::t('TourModule.profile', '<strong>Edit</strong> account')); ?>,
                        content: <?= json_encode(Yii::t('TourModule.profile', 'Click on this button to update your profile and account settings. You can also add more information to your profile.')); ?>,
                        placement: "left"
                    },
                    {
                        element: ".layout-nav-container .panel",
                        title: <?= json_encode(Yii::t('TourModule.profile', '<strong>Profile</strong> menu')); ?>,
                        content: <?= json_encode(Yii::t('TourModule.profile', 'Just like in the space, the user profile can be personalized with various modules.<br><br>You can see which modules are available for your profile by looking them in “Modules” in the account settings menu.')); ?>,
                        placement: "right"
                    },
                    {
                        element: "#contentFormBody",
                        title: <?= json_encode(Yii::t('TourModule.profile', '<strong>Profile</strong> stream')); ?>,
                        content: <?= json_encode(Yii::t('TourModule.profile', 'Each profile has its own pin board. Your posts will also appear on the dashboards of those users who are following you.')); ?>,
                        placement: "bottom"
                    },
                    <?php if ($isAmind) : ?>
                        {
                            orphan: true,
                            backdrop: true,
                            title: <?= json_encode(Yii::t('TourModule.profile', '<strong>Hurray!</strong> You\'re done!')); ?>,
                            content: <?= json_encode(Yii::t('TourModule.profile', 'You\'ve completed the user profile guide!<br><br>To carry on with the administration guide, click here:<br /><br />')); ?> + "<a href='#' data-action-click='tour.next'><?= Yii::t("TourModule.profile", "<strong>Administration (Modules)</strong>"); ?></a><br><br>"
                        }
                    <?php else : ?>
                        {
                            orphan: true,
                            backdrop: true,
                            title: <?= json_encode(Yii::t('TourModule.profile', '<strong>Hurray!</strong> The End.')); ?>,
                            content: <?= json_encode(Yii::t('TourModule.profile', "You've completed the user profile guide!")); ?>
                        }
                    <?php endif; ?>

                ]
            }
        );
    });
</script>
