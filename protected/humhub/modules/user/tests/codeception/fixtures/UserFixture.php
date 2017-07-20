<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\tests\codeception\fixtures;

use yii\test\ActiveFixture;

class UserFixture extends ActiveFixture
{

    public $modelClass = 'humhub\modules\user\models\User';
    public $dataFile = '@modules/user/tests/codeception/fixtures/data/user.php';
    public $depends = [
        'humhub\modules\user\tests\codeception\fixtures\UserProfileFixture',
        'humhub\modules\content\tests\codeception\fixtures\ContentContainerFixture',
        'humhub\modules\user\tests\codeception\fixtures\UserPasswordFixture',
        'humhub\modules\user\tests\codeception\fixtures\UserFollowFixture',
        'humhub\modules\user\tests\codeception\fixtures\UserModuleFixture',
        'humhub\modules\user\tests\codeception\fixtures\GroupFixture'
    ];

}
