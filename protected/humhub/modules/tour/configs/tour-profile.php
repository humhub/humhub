<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\tour\models\TourParams;
use humhub\modules\user\controllers\ProfileController;
use humhub\widgets\bootstrap\Button;

return [
    TourParams::KEY_PAGE => TourParams::PAGE_PROFILE,
    TourParams::KEY_CONTROLLER_CLASS => ProfileController::class,
    TourParams::KEY_TITLE => Yii::t('TourModule.base', '<strong>Guide:</strong> User profile'),
    TourParams::KEY_URL => Yii::$app->user->identity->createUrl('/user/profile/home', ['tour' => true]),
    TourParams::KEY_NEXT_PAGE => Yii::$app->user?->isAdmin() ? TourParams::PAGE_ADMINISTRATION : null,
    TourParams::KEY_DRIVER => [
        'steps' => [
            [
                'popover' => [
                    'title' => Yii::t('TourModule.profile', '<strong>User profile</strong>'),
                    'description' => Yii::t('TourModule.profile', "This is your public user profile, which can be seen by any registered user."),
                ],
            ],
            [
                'element' => ".profile-user-photo-container",
                'popover' => [
                    'title' => Yii::t('TourModule.profile', '<strong>Profile</strong> photo'),
                    'description' => Yii::t('TourModule.profile', 'Upload a new profile photo by simply clicking here or by drag&drop. Do just the same for updating your cover photo.'),
                ],
            ],
            [
                'element' => ".edit-account",
                'popover' => [
                    'title' => Yii::t('TourModule.profile', '<strong>Edit</strong> account'),
                    'description' => Yii::t('TourModule.profile', 'Click on this button to update your profile and account settings. You can also add more information to your profile.'),
                ],
            ],
            [
                'element' => ".layout-nav-container .panel",
                'popover' => [
                    'title' => Yii::t('TourModule.profile', '<strong>Profile</strong> menu'),
                    'description' => Yii::t('TourModule.profile', 'Just like in the space, the user profile can be personalized with various modules.<br><br>You can see which modules are available for your profile by looking them in "Modules" in the account settings menu.'),
                ],
            ],
            [
                'element' => "#contentFormBody",
                'popover' => [
                    'title' => Yii::t('TourModule.profile', '<strong>Profile</strong> stream'),
                    'description' => Yii::t('TourModule.profile', 'Each profile has its own pin board. Your posts will also appear on the dashboards of those users who are following you.'),
                ],
            ],
            [
                'popover' => Yii::$app->user->isAdmin() ?
                    [
                        'title' => Yii::t('TourModule.profile', '<strong>Hurray!</strong> You\'re done!'),
                        'description' =>
                            Yii::t('TourModule.profile', 'You\'ve completed the user profile guide!<br><br>To carry on with the administration guide, click here:<br /><br />') .
                            Button::asLink(Yii::t("TourModule.profile", "<strong>Administration (Modules)</strong>"))->action('tour.next') .
                            '<br><br>',
                    ] :
                    [
                        'title' => Yii::t('TourModule.profile', '<strong>Hurray!</strong> The End.'),
                        'description' => Yii::t('TourModule.profile', "You've completed the user profile guide!"),
                    ],
            ],
        ],
    ],
];
