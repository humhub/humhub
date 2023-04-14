<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\post\tests\codeception\fixtures;

use tests\codeception\_support\ContentActiveFixture;

class PostFixture extends ContentActiveFixture
{
    public $modelClass = 'humhub\modules\post\models\Post';
    public $dataFile = '@modules/post/tests/codeception/fixtures/data/post.php';
}
