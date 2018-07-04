<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\content\tests\codeception\fixtures;

use humhub\modules\content\models\ContentContainerModuleState;
use yii\test\ActiveFixture;

class ContentContainerModuleFixture extends ActiveFixture
{
    public $modelClass = ContentContainerModuleState::class;
    public $dataFile = '@modules/content/tests/codeception/fixtures/data/contentcontainer_module.php';
}
