<?php

namespace humhub\modules\post;

class Events extends \yii\base\Object
{

    public static function onLoad()
    {
        print "cb";
        die();
    }

}
