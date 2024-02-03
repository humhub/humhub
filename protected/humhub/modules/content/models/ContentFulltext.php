<?php

namespace humhub\modules\content\models;

use humhub\components\ActiveRecord;

class ContentFulltext extends ActiveRecord
{
    public static function tableName()
    {
        return 'content_fulltext';
    }

}
