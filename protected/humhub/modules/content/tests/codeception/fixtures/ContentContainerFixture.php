<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\tests\codeception\fixtures;

use yii\test\ActiveFixture;

class ContentContainerFixture extends ActiveFixture
{
    public $modelClass = 'humhub\modules\content\models\ContentContainer';
    public $dataFile = '@modules/content/tests/codeception/fixtures/data/contentcontainer.php';
    
    public $depends = [
        'humhub\modules\content\tests\codeception\fixtures\ContentContainerPermissionFixture',
        'humhub\modules\content\tests\codeception\fixtures\ContentContainerSettingFixture'
    ];

}
