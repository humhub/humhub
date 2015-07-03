<?php

namespace humhub\modules\user;

class Events extends \yii\base\Object
{

    /**
     * On rebuild of the search index, rebuild all user records
     *
     * @param type $event
     */
    public static function onSearchRebuild($event)
    {
        foreach (models\User::find()->all() as $obj) {
            \Yii::$app->search->add($obj);
        }
    }

    /**
     * On delete of a Content or ContentAddon
     *
     * @param type $event
     */
    public static function onContentDelete($event)
    {
        models\Mentioning::deleteAll(['object_model' => $event->sender->className(), 'object_id' => $event->sender->getPrimaryKey()]);
    }

}
