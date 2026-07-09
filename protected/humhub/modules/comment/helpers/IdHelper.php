<?php

namespace humhub\modules\comment\helpers;

use humhub\modules\comment\models\Comment;
use humhub\modules\content\models\Content;

class IdHelper
{
    public static function getId(Content $content, ?Comment $parentComment)
    {
        return 'C' . $content->id . "P" . $parentComment?->id;
    }

}
