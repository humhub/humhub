<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\dashboard\controllers\DashboardController;
use humhub\modules\tour\models\TourConfig;
use humhub\widgets\bootstrap\Button;
use yii\helpers\Url;

return [
    TourConfig::KEY_PAGE => TourConfig::PAGE_DASHBOARD,
    TourConfig::KEY_CONTROLLER_CLASS => DashboardController::class,
    TourConfig::KEY_TITLE => Yii::t('TourModule.base', '<strong>Guide:</strong> Overview'),
    TourConfig::KEY_URL => Url::to(['/dashboard/dashboard', 'tour' => true]),
    TourConfig::KEY_NEXT_PAGE => TourConfig::PAGE_SPACES,
    TourConfig::KEY_DRIVER => [
        'steps' => [
            [
                'popover' => [
                    'title' => Yii::t('TourModule.interface', '<strong>Dashboard</strong>'),
                    'description' => Yii::t('TourModule.interface', "This is your dashboard.<br><br>Any new activities or posts that might interest you will be displayed here."),
                ],
            ],
            [
                'element' => "#icon-notifications",
                'popover' => [
                    'title' => Yii::t('TourModule.base', '<strong>Notifications</strong>'),
                    'description' => Yii::t('TourModule.base', 'Don\'t lose track of things!<br /><br />This icon will keep you informed of activities and posts that concern you directly.'),
                ],
            ],
            [
                'element' => ".dropdown.account",
                'popover' => [
                    'title' => Yii::t('TourModule.base', '<strong>Account</strong> Menu'),
                    'description' => Yii::t('TourModule.base', 'The account menu gives you access to your private settings and allows you to manage your public profile.'),
                ],
            ],
            [
                'element' => "#space-menu",
                'popover' => [
                    'title' => Yii::t('TourModule.base', '<strong>Space</strong> Menu'),
                    'description' =>
                        Yii::t('TourModule.base', 'This is the most important menu and will probably be the one you use most often!<br><br>Access all the spaces you have joined and create new spaces here.<br><br>The next guide will show you how:') .
                        '<br><br>' .
                        Button::asLink(Yii::t("TourModule.base", "<strong>Start</strong> space guide"))->action('tour.next') .
                        '<br><br>',
                ],
            ],
        ],
    ],
];
