<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\space\controllers\SpaceController;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\tour\TourConfig;
use humhub\widgets\bootstrap\Button;

// Get Space to run Tour in
$tourSpace = null;

// Loop over all spaces where the user is member
foreach (Membership::getUserSpaces() as $tourSpace) {
    if ($tourSpace->isAdmin() && !$tourSpace->isArchived()) {
        // If user is admin on this space, it´s the perfect match
        break;
    }
}

if ($tourSpace === null) {
    // If user is not member of any space, try to find a public space to run Tour in
    $tourSpace = Space::findOne(['and', ['!=', 'visibility' => Space::VISIBILITY_NONE], ['status' => Space::STATUS_ENABLED]]);
}

return [
    TourConfig::KEY_TOUR_ID => TourConfig::TOUR_ID_SPACES,
    TourConfig::KEY_IS_VISIBLE => function () use ($tourSpace) {
        return (bool)$tourSpace;
    },
    TourConfig::KEY_TOUR_ON_CONTROLLER_CLASS => SpaceController::class,
    TourConfig::KEY_TITLE => function () {
        return Yii::t('TourModule.base', '<strong>Guide:</strong> Spaces');
    },
    TourConfig::KEY_START_URL => function () use ($tourSpace) {
        return $tourSpace?->createUrl('/space/space', ['tour' => true]);
    },
    TourConfig::KEY_NEXT_TOUR_ID => TourConfig::TOUR_ID_PROFILE,
    TourConfig::KEY_DRIVER_JS => [
        'steps' => [
            [
                'popover' => [
                    'title' => Yii::t('TourModule.spaces', '<strong>Space</strong>'),
                    'description' => Yii::t('TourModule.spaces', "Once you have joined or created a new space you can work on projects, discuss topics or just share information with other users.<br><br>There are various tools to personalize a space, thereby making the work process more productive."),
                ],
            ],
            [
                'element' => ".layout-nav-container .panel",
                'popover' => [
                    'title' => Yii::t('TourModule.spaces', '<strong>Space</strong> navigation menu'),
                    'description' => Yii::t('TourModule.spaces', 'This is where you can navigate the space – where you find which modules are active or available for the particular space you are currently in. These could be polls, tasks or notes for example.<br><br>Only the space admin can manage the space\'s modules.'),
                ],
            ],
            [
                'element' => ".dropdown",
                'popover' => [
                    'title' => Yii::t('TourModule.spaces', '<strong>Space</strong> preferences'),
                    'description' => Yii::t('TourModule.spaces', 'This menu is only visible for space admins. Here you can manage your space settings, add/block members and activate/deactivate tools for this space.'),
                ],
            ],
            [
                'element' => "#contentFormBody",
                'popover' => [
                    'title' => Yii::t('TourModule.spaces', '<strong>Writing</strong> posts'),
                    'description' => Yii::t('TourModule.spaces', 'New posts can be written and posted here.'),
                ],
            ],
            [
                'element' => ".wall-entry:first-of-type",
                'popover' => [
                    'title' => Yii::t('TourModule.spaces', '<strong>Posts</strong>'),
                    'description' => Yii::t('TourModule.spaces', 'Yours, and other users\' posts will appear here.<br><br>These can then be liked or commented on.'),
                ],
            ],
            [
                'element' => ".panel-activities",
                'popover' => [
                    'title' => Yii::t('TourModule.spaces', '<strong>Most recent</strong> activities'),
                    'description' => Yii::t('TourModule.spaces', 'To keep you up to date, other users\' most recent activities in this space will be displayed here.'),
                ],
            ],
            [
                'element' => "#space-members-panel",
                'popover' => [
                    'title' => Yii::t('TourModule.spaces', '<strong>Space</strong> members'),
                    'description' => Yii::t('TourModule.spaces', 'All users who are a member of this space will be displayed here.<br /><br />New members can be added by anyone who has been given access rights by the admin.'),
                ],
            ],
            [
                'popover' => [
                    'title' => Yii::t('TourModule.spaces', '<strong>Yay! You\'re done.</strong>'),
                    'description' =>
                        Yii::t('TourModule.spaces', "That's it for the space guide.<br><br>To carry on with the user profile guide, click here: ") .
                        Button::asLink(Yii::t("TourModule.spaces", "<strong>Profile Guide</strong>"))->action('tour.next') .
                        '<br><br>',
                ],
            ],
        ],
    ],
];
