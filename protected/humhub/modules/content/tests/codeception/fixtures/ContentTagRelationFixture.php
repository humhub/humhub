<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\content\tests\codeception\fixtures;

use humhub\modules\content\models\ContentTag;
use humhub\modules\content\models\ContentTagRelation;
use yii\test\ActiveFixture;

class ContentTagRelationFixture extends ActiveFixture
{
    public $modelClass = ContentTagRelation::class;
    public $dataFile = '@modules/content/tests/codeception/fixtures/data/content_tag_relation.php';
}
