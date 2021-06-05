<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\content\tests\codeception\fixtures;

use humhub\modules\content\models\ContentContainerTag;
use yii\test\ActiveFixture;

class ContentContainerTagFixture extends ActiveFixture
{
    public $modelClass = ContentContainerTag::class;
    public $dataFile = '@modules/content/tests/codeception/fixtures/data/contentcontainer_tag.php';
}
