<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\content\tests\codeception\fixtures;

use humhub\modules\content\models\ContentTag;
use yii\test\ActiveFixture;

class ContentTagFixture extends ActiveFixture
{
    public $modelClass = ContentTag::class;
    public $dataFile = '@modules/content/tests/codeception/fixtures/data/content_tag.php';
}
