<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\admin\permissions\ManageSpaces;
use humhub\modules\space\controllers\SpaceController;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\tour\TourConfig;
use humhub\widgets\bootstrap\Link;

// Get Space to run Tour in
$tourSpace = null;

$user = Yii::$app->user->getIdentity();
if ($user !== null) {
    $tourSpaceQuery = Space::find()
        ->innerJoin(Membership::tableName(), Membership::tableName() . '.space_id = ' . Space::tableName() . '.id')
        ->where([
            Membership::tableName() . '.user_id' => $user->id,
            Membership::tableName() . '.status' => Membership::STATUS_MEMBER,
            Space::tableName() . '.status' => Space::STATUS_ENABLED,
        ]);

    if (!Yii::$app->user->can(ManageSpaces::class)) {
        $tourSpaceQuery->andWhere([
            'OR',
            [Space::tableName() . '.created_by' => $user->id],
            [Membership::tableName() . '.group_id' => Space::USERGROUP_ADMIN],
        ]);
    }

    $tourSpaceQuery->orderBy(Yii::$app->getModule('space')->settings->get('spaceOrder') == 0
        ? [Space::tableName() . '.name' => SORT_ASC]
        : [Membership::tableName() . '.last_visit' => SORT_DESC]);

    $tourSpace = $tourSpaceQuery->limit(1)->one();
}

if ($tourSpace === null) {
    // If user doesn't manage any space, try to find a visible space to run Tour in
    $tourSpace = Space::find()
        ->where(['!=', 'visibility', Space::VISIBILITY_NONE])
        ->andWhere(['status' => Space::STATUS_ENABLED])
        ->one();
}

return [
    TourConfig::KEY_TOUR_ID => TourConfig::TOUR_ID_SPACES,
    TourConfig::KEY_IS_VISIBLE => (bool)$tourSpace,
    TourConfig::KEY_TOUR_ON_CONTROLLER_CLASS => SpaceController::class,
    TourConfig::KEY_TITLE => Yii::t('TourModule.base', '<strong>Guide:</strong> Spaces'),
    TourConfig::KEY_START_URL => $tourSpace?->createUrl('/space/space', ['tour' => true]),
    TourConfig::KEY_NEXT_TOUR_ID => TourConfig::TOUR_ID_PROFILE,
    TourConfig::KEY_DRIVER_JS => [
        'steps' => [
            [
                'popover' => [
                    'title' => Yii::t('TourModule.spaces', '<strong>Space</strong>'),
                    'description' => Yii::t('TourModule.spaces', "Once you have joined or created a new Space you can work on projects, discuss topics or just share information with other users.<br><br>There are various tools to personalize a Space, thereby making the work process more productive."),
                ],
            ],
            [
                'element' => ".layout-nav-container .panel",
                'popover' => [
                    'title' => Yii::t('TourModule.spaces', '<strong>Space</strong> navigation menu'),
                    'description' => Yii::t('TourModule.spaces', 'This is where you can navigate the Space – where you find which modules are active or available for the particular Space you are currently in. These could be polls, tasks or notes for example.<br><br>Only the Space admin can manage the space\'s modules.'),
                ],
            ],
            [
                'element' => "#space-header-controls-menu",
                'popover' => [
                    'title' => Yii::t('TourModule.spaces', '<strong>Space</strong> preferences'),
                    'description' => Yii::t('TourModule.spaces', 'This menu is only visible for Space admins. Here you can manage your Space settings, add/block members and activate/deactivate tools for this Space.'),
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
                    'description' => Yii::t('TourModule.spaces', 'To keep you up to date, other users\' most recent activities in this Space will be displayed here.'),
                ],
            ],
            [
                'element' => "#space-members-panel",
                'popover' => [
                    'title' => Yii::t('TourModule.spaces', '<strong>Space</strong> members'),
                    'description' => Yii::t('TourModule.spaces', 'All users who are a member of this Space will be displayed here.<br /><br />New members can be added by anyone who has been given access rights by the admin.'),
                ],
            ],
            [
                'popover' => [
                    'title' => Yii::t('TourModule.spaces', '<strong>Yay! You\'re done.</strong>'),
                    'description'
                        => Yii::t('TourModule.spaces', "That's it for the Space guide.<br><br>To carry on with the user profile guide, click here: ")
                        . Link::to(Yii::t("TourModule.spaces", "<strong>Profile Guide</strong>"))
                            ->encodeLabel(false)
                            ->action('tour.next')
                        . '<br><br>',
                ],
            ],
        ],
    ],
];
