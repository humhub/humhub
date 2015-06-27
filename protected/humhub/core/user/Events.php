<?php

namespace humhub\core\user;

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

}
