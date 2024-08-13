<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\content\tests\codeception\fixtures;

use humhub\modules\content\models\ContentContainerTagRelation;
use yii\test\ActiveFixture;

class ContentContainerTagRelationFixture extends ActiveFixture
{
    public $modelClass = ContentContainerTagRelation::class;
    public $dataFile = '@modules/content/tests/codeception/fixtures/data/contentcontainer_tag_relation.php';
}
