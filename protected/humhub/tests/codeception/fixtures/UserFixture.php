<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\fixtures;

use yii\test\ActiveFixture;

class UserFixture extends ActiveFixture
{

    public $modelClass = 'humhub\modules\user\models\User';
    public $dataFile = '@modules/user/tests/codeception/fixtures/data/user.php';
    public $depends = [
        'tests\codeception\fixtures\ContentContainerFixture',
        'tests\codeception\fixtures\UserPasswordFixture',
        'tests\codeception\fixtures\GroupFixture'
    ];

}
