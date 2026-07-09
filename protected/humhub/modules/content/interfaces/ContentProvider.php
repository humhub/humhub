<?php

namespace humhub\modules\content\interfaces;

use humhub\modules\content\models\Content;
use yii\db\ActiveQuery;

/**
 * @property-read Content $content
 */
interface ContentProvider
{
    public function getContent(): ActiveQuery;
}
