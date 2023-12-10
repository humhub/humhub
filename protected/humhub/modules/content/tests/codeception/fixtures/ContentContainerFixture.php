<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\tests\codeception\fixtures;

use humhub\modules\content\models\ContentContainer;
use humhub\tests\codeception\fixtures\GlobalIdFixture;
use yii\test\ActiveFixture;

class ContentContainerFixture extends ActiveFixture
{
    public $modelClass = ContentContainer::class;
    public $dataFile = '@modules/content/tests/codeception/fixtures/data/contentcontainer.php';

    public $depends = [
        GlobalIdFixture::class,
        ContentContainerDefaultPermissionFixture::class,
        ContentContainerPermissionFixture::class,
        ContentContainerSettingFixture::class,
        ContentContainerModuleFixture::class
    ];
}
