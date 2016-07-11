<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\fixtures;

use yii\test\ActiveFixture;

class UserPasswordFixture extends ActiveFixture
{

    public $modelClass = 'humhub\modules\user\models\Password';
    public $dataFile = '@tests/codeception/fixtures/data/user_password.php';
    public $depends = [
        'tests\codeception\fixtures\UserFixture'
    ];

}
