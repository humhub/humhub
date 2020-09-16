<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace tests\codeception\unit\modules\content;

use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;

use humhub\modules\space\models\Space;
use Yii;

class ContentContainerActiveRecordTest extends HumHubDbTestCase
{

    public function testUserIsNotASpace()
    {
        $user = User::findOne(['id' => 1]);
        $space = Space::findOne(['id' => 2]);

        $this->assertFalse($user->is($space));
    }

    public function testSpaceIsSameSpace()
    {
        $space = Space::findOne(['id' => 1]);
        $space1 = Space::findOne(['id' => 1]);

        $this->assertTrue($space->is($space1));
    }

    public function testUserIsNotAnotherUser()
    {
        $user = User::findOne(['id' => 1]);
        $user2 = User::findOne(['id' => 2]);

        $this->assertFalse($user->is($user2));
    }

    public function testUserIsSameUser()
    {
        $user = User::findOne(['id' => 1]);
        $user1 = User::findOne(['id' => 1]);

        $this->assertTrue($user->is($user1));
    }

    public function testGuestISNotUser()
    {
        $user = User::findOne(['id' => 1]);

        $this->assertFalse($user->is(Yii::$app->user->getIdentity()));
    }

    public function testNullISNotUser()
    {
        $space = Space::findOne(['id' => 1]);

        $this->assertFalse($space->is(null));
    }
}
