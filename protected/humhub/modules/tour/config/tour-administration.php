<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\admin\controllers\ModuleController;
use humhub\modules\tour\TourConfig;
use yii\helpers\Url;

return [
    TourConfig::KEY_TOUR_ID => TourConfig::TOUR_ID_ADMINISTRATION,
    TourConfig::KEY_IS_VISIBLE => function () {
        return Yii::$app->user->isAdmin();
    },
    TourConfig::KEY_TOUR_ON_CONTROLLER_CLASS => ModuleController::class,
    TourConfig::KEY_TITLE => function () {
        return Yii::t('TourModule.base', '<strong>Guide:</strong> Administration (Modules)');
    },
    TourConfig::KEY_START_URL => function () {
        return Url::to(['/admin/module/list', 'tour' => true]);
    },
    TourConfig::KEY_NEXT_TOUR_ID => null,
    TourConfig::KEY_DRIVER_JS => [
        'steps' => [
            [
                'popover' => [
                    'title' => Yii::t('TourModule.administration', '<strong>Administration</strong>'),
                    'description' => Yii::t('TourModule.administration', "As an admin, you can manage the whole platform from here.<br><br>Apart from the modules, we are not going to go into each point in detail here, as each has its own short description elsewhere."),
                ],
            ],
            [
                'element' => ".list-group-item.modules",
                'popover' => [
                    'title' => Yii::t('TourModule.administration', '<strong>Modules</strong>'),
                    'description' => Yii::t('TourModule.administration', 'You are currently in the tools menu. From here you can access the HumHub online marketplace, where you can install an ever increasing number of tools on-the-fly.<br><br>As already mentioned, the tools increase the features available for your space.'),
                ],
            ],
            [
                'popover' => [
                    'title' => Yii::t('TourModule.administration', "<strong>Hurray!</strong> That's all for now."),
                    'description' => Yii::t('TourModule.administration', 'You have now learned about all the most important features and settings and are all set to start using the platform.<br><br>We hope you and all future users will enjoy using this site. We are looking forward to any suggestions or support you wish to offer for our project. Feel free to contact us via www.humhub.org.<br><br>Stay tuned. :-)'),
                ],
            ],
        ],
    ],
];
