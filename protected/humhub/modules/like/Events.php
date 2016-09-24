<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\like;

use humhub\modules\like\models\Like;

/**
 * Events provides callbacks to handle events.
 * 
 * @author luke
 */
class Events extends \yii\base\Object
{

    /**
     * On User delete, also delete all comments
     *
     * @param type $event
     */
    public static function onUserDelete($event)
    {
        foreach (Like::findAll(array('created_by' => $event->sender->id)) as $like) {
            $like->delete();
        }

        return true;
    }

    public static function onActiveRecordDelete($event)
    {
        $record = $event->sender;
        if ($record->hasAttribute('id')) {
            foreach (Like::findAll(array('object_id' => $record->id, 'object_model' => $record->className())) as $like) {
                $like->delete();
            }
        }
    }

    /**
     * Callback to validate module database records.
     *
     * @param Event $event
     */
    public static function onIntegrityCheck($event)
    {
        $integrityController = $event->sender;
        $integrityController->showTestHeadline("Like (" . Like::find()->count() . " entries)");

        foreach (Like::find()->all() as $like) {
            if ($like->source === null) {
                if ($integrityController->showFix("Deleting like id " . $like->id . " without existing target!")) {
                    $like->delete();
                }
            }
            // User exists
            if ($like->user === null) {
                if ($integrityController->showFix("Deleting like id " . $like->id . " without existing user!")) {
                    $like->delete();
                }
            }
        }
    }

    /**
     * On initalizing the wall entry controls also add the like link widget
     *
     * @param type $event
     */
    public static function onWallEntryLinksInit($event)
    {
        $event->sender->addWidget(widgets\LikeLink::className(), array('object' => $event->sender->object), array('sortOrder' => 10));
    }

}
