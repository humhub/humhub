<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\tests\codeception\fixtures;

use humhub\modules\space\tests\codeception\fixtures\SpaceFixture;
use yii\test\ActiveFixture;

class GroupFixture extends ActiveFixture
{

    public $modelClass = 'humhub\modules\user\models\Group';
    public $dataFile = '@modules/user/tests/codeception/fixtures/data/group.php';
    
    public $depends = [
        UserFixture::class,
        SpaceFixture::class
    ];

}
