<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\stream\actions;

use humhub\modules\stream\models\GlobalStreamQuery;

/**
 * GlobalStream is used to stream global content.
 *
 * @since 1.16
 */
class GlobalStream extends Stream
{
    /**
     * @inheritdoc
     */
    public $streamQueryClass = GlobalStreamQuery::class;
}
