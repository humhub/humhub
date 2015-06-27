<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\core\search;

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

        if ($comment->content->getUnderlyingObject() instanceof ISearchable) {
            Yii::app()->search->update($comment->content->getUnderlyingObject());
        }
    }

    public static function onConsoleApplicationInit($event)
    {

        $application = $event->sender;
        $application->controllerMap['search'] = commands\SearchController::className();
    }

}
