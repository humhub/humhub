<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/**
 * Description of SearchModuleEvents
 *
 * @author luke
 */
class SearchModuleEvents
{

    public static function onTopMenuRightInit($event)
    {
        $event->sender->addWidget('application.modules_core.search.widgets.SearchMenuWidget');
    }

    public static function onAfterSaveComment($event)
    {
        $comment = $event->sender;

        if ($comment->content->getUnderlyingObject() instanceof ISearchable) {
            Yii::app()->search->update($comment->content->getUnderlyingObject());
        }
    }

}
