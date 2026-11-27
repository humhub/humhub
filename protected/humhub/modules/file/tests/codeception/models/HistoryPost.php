<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\tests\codeception\models;

use humhub\modules\post\models\Post;

/**
 * A post that keeps the history of its files, as e.g. a cfiles file does - core has no such record.
 */
class HistoryPost extends Post
{
    /**
     * @inheritdoc
     */
    public $fileManagerEnableHistory = true;
}
