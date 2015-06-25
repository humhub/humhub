<?php

namespace humhub\core\user;

class Events extends \yii\base\Object
{

    public static function onLoad()
    {
        print "cb";
        die();
    }

}
