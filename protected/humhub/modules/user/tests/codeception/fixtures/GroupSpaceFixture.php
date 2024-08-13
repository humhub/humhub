<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\tests\codeception\fixtures;

use humhub\modules\space\tests\codeception\fixtures\SpaceFixture;
use humhub\modules\user\models\GroupSpace;
use yii\test\ActiveFixture;

class GroupSpaceFixture extends ActiveFixture
{

    public $modelClass = GroupSpace::class;
    public $dataFile = '@modules/user/tests/codeception/fixtures/data/group_space.php';

    public $depends = [
        UserFixture::class,
        SpaceFixture::class
    ];

}
