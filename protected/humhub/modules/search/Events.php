<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\search;

use Yii;
use yii\base\BaseObject;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * Search module event callbacks
 *
 * @author luke
 */
class Events extends BaseObject
{

    public static function onTopMenuRightInit($event)
    {
        $event->sender->addWidget(widgets\SearchMenu::class);
    }

    public static function onHourlyCron($event)
    {
        /** @var Controller $controller */
        $controller = $event->sender;

        $controller->stdout('Optimizing search index...');
        Yii::$app->search->optimize();
        $controller->stdout('done.' . PHP_EOL, Console::FG_GREEN);
    }

    public static function onConsoleApplicationInit($event)
    {
        $application = $event->sender;
        $application->controllerMap['search'] = commands\SearchController::class;
    }

}
