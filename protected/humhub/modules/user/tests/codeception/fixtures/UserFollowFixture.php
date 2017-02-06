<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\tests\codeception\fixtures;

use yii\test\ActiveFixture;

class UserFollowFixture extends ActiveFixture
{

    public $modelClass = 'humhub\modules\user\models\Follow';
    public $dataFile = '@modules/user/tests/codeception/fixtures/data/user_follow.php';

}
