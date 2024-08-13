<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\tests\codeception\fixtures;

use yii\test\ActiveFixture;

class ContentContainerDefaultPermissionFixture extends ActiveFixture
{
    public $modelClass = 'humhub\modules\content\models\ContentContainerDefaultPermission';
    public $dataFile = '@modules/content/tests/codeception/fixtures/data/contentcontainer_default_permission.php';
}
