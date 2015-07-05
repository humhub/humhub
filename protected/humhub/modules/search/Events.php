<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\search;

use Yii;

/**
 * Description of SearchModuleEvents
 *
 * @author luke
 */
class Events extends \yii\base\Object
{

    public static function onTopMenuRightInit($event)
    {
        $event->sender->addWidget(widgets\SearchMenu::className());
    }

    public static function onAfterSaveComment($event)
    {
        $comment = $event->sender;

        if ($comment->content->getPolymorphicRelation() instanceof ISearchable) {
            Yii::app()->search->update($comment->content->getPolymorphicRelation());
        }
    }

    public static function onHourlyCron($event)
    {
        $controller = $event->sender;
        $controller->stdout("Optimizing search index... ");
        Yii::$app->search->optimize();
        $controller->stdout('done.' . PHP_EOL, \yii\helpers\Console::FG_GREEN);
    }

    public static function onConsoleApplicationInit($event)
    {

        $application = $event->sender;
        $application->controllerMap['search'] = commands\SearchController::className();
    }

}
