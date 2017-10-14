<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\user\tests\codeception\fixtures;

use yii\test\ActiveFixture;

class UserFullFixture extends ActiveFixture
{

    public $tableName = 'user_mentioning';
    public $depends = [
        UserFixture::class,
        'humhub\modules\user\tests\codeception\fixtures\UserProfileFixture',
        'humhub\modules\content\tests\codeception\fixtures\ContentContainerFixture',
        'humhub\modules\user\tests\codeception\fixtures\UserPasswordFixture',
        'humhub\modules\user\tests\codeception\fixtures\UserFollowFixture',
        'humhub\modules\user\tests\codeception\fixtures\UserModuleFixture',
        'humhub\modules\user\tests\codeception\fixtures\GroupFixture'
    ];

}
