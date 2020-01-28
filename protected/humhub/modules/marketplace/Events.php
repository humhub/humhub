<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace;

use Yii;
use yii\base\BaseObject;
use yii\base\Event;
use yii\helpers\Url;

class Events extends BaseObject
{

    /**
     * On console application initialization
     *
     * @param Event $event
     */
    public static function onConsoleApplicationInit($event)
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('marketplace');

        if (!$module->enabled) {
            return;
        }

        $application = $event->sender;
        $application->controllerMap['module'] = commands\MarketplaceController::class;
    }

    public static function onAdminModuleMenuInit($events)
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('marketplace');

        if (!$module->enabled) {
            return;
        }

        $updatesBadge = '';
        $updatesCount = count($module->onlineModuleManager->getModuleUpdates());
        if ($updatesCount > 0) {
            $updatesBadge = '&nbsp;&nbsp;<span class="label label-danger">' . $updatesCount . '</span>';
        } else {
            $updatesBadge = '&nbsp;&nbsp;<span class="label label-default">0</span>';
        }

        $events->sender->addItem([
            'label' => Yii::t('MarketplaceModule.base', 'Browse online'),
            'url' => Url::to(['/marketplace/browse']),
            'sortOrder' => 200,
            'isActive' => (Yii::$app->controller->id == 'browse'),
        ]);

        $events->sender->addItem([
            'label' => Yii::t('MarketplaceModule.base', 'Purchases'),
            'url' => Url::to(['/marketplace/purchase']),
            'sortOrder' => 300,
            'isActive' => (Yii::$app->controller->id == 'purchase'),
        ]);

        $events->sender->addItem([
            'label' => Yii::t('MarketplaceModule.base', 'Available updates') . $updatesBadge,
            'url' => Url::to(['/marketplace/update']),
            'sortOrder' => 400,
            'isActive' => (Yii::$app->controller->id == 'update'),
        ]);

    }

    public static function onHourlyCron($event)
    {
        Yii::$app->queue->push(new jobs\PeActiveCheckJob());
        Yii::$app->queue->push(new jobs\ModuleCleanupsJob());
    }

}
