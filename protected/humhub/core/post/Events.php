<?php

namespace humhub\core\post;

class Events extends \yii\base\Object
{

    public static function onLoad()
    {
        print "cb";
        die();
    }

}
