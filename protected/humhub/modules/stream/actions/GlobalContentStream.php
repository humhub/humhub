<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\stream\actions;

use humhub\modules\stream\models\GlobalContentStreamQuery;

/**
 * GlobalContentStream is used to stream global content.
 *
 * @since 1.16
 */
class GlobalContentStream extends Stream
{
    /**
     * @inheritdoc
     */
    public $streamQueryClass = GlobalContentStreamQuery::class;
}
