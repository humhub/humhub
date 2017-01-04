<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\tests\codeception\fixtures;

use yii\test\ActiveFixture;

class ContentContainerSettingFixture extends ActiveFixture
{

    public $modelClass = 'humhub\modules\content\models\ContentContainerSetting';
    public $dataFile = '@modules/content/tests/codeception/fixtures/data/contentcontainer_setting.php';

}
